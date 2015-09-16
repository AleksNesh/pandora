<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_GoogleBaseFeedGenerator
 * @copyright Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 *
 * @method getOutputColumns() array What columns to process and output in the map, defaults to empty (process all columns)
 */
class RocketWeb_GoogleBaseFeedGenerator_Model_Generator extends Varien_Object
{

    const PRODUCT_TYPE_ASSOC = 'simple_associated';

    protected $_handle = null;
    protected $_count_products_exported = 0;
    protected $_count_products_skipped = 0;

    protected $_columns_map = null;
    protected $_empty_columns_replace_map = null;

    protected $_collection = null;
    protected $_total_items = null;
    protected $_current_iter = 0;

    protected $_currencyObject;
    protected $_currencyRate;

    /**
     * @var RocketWeb_GoogleBaseFeedGenerator_Model_Batch
     */
    protected $batch;
    protected $_storeLockFile;

    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('store_code')) {
            $this->setData('store_code', Mage_Core_Model_Store::DEFAULT_CODE); 
        }
        try {
            Mage::app()->getStore($this->getData('store_code'));
        } catch (Exception $e) {
            Mage::throwException(sprintf('Store with code \'%s\' doesn\'t exists.', $this->getData('store_code')));
        }

        $this->setData('store_id', Mage::app()->getStore($this->getData('store_code'))->getStoreId());
        $this->setData('website_id', Mage::app()->getStore($this->getData('store_code'))->getWebsiteId());
        $this->setData('store_currency_code', Mage::app()->getStore($this->getData('store_code'))->getDefaultCurrencyCode());

        if (!$this->getSkipLocks()) {

            // Initialize locks
            $this->initSavePath();
            $this->_storeLockFile = @fopen($this->getLockPath(), "w");
            if (!file_exists($this->getLockPath())) {
                Mage::throwException(sprintf('Can\'t create file %s', $this->getLockPath()));
            }

            // If the location is not writable, flock() does not work and it doesn't mean another script instance is running
            if (!is_writable($this->getLockPath())) {
                Mage::throwException(sprintf('Not enough permissions. Location [%s] must be writable', $this->getLockPath()));
            }
        }
    }

    protected function initialize()
    {
        $this->getColumnsMap();
        $this->getEmptyColumnsReplaceMap();
        $this->loadAdditionalAttributes();
        return $this;
    }

    /**
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Batch
     */
    public function getBatch()
    {
        if ($this->getBatchMode() && is_null($this->batch)) {
            if (!$this->getScheduleId()) {
                Mage::throwException(sprintf('Invalid schedule_id %s', $this->getScheduleId())); 
            }
            $this->batch = Mage::getModel(
                'googlebasefeedgenerator/batch', array(
                'store_code' => $this->getStoreCode(),
                'store_id' => $this->getStoreId(),
                'website_id' => $this->getWebsiteId(),
                'config' => $this->getConfig(),
                'schedule_id' => $this->getScheduleId(),
                )
            );
        }

        return $this->batch;
    }

    /**
     * @return $this
     */
    public function run()
    {
        $time   = time();
        $memory = memory_get_usage(true);

        if (!$this->getConfig()->isEnabled($this->getStoreId())) {
            return $this;
        }

        // Another instance is writing to the feed
        if (!$this->acquireLock()) {
            Mage::throwException(sprintf('Another generator instance is writing the feed for store [%s]. Try again later.', $this->getStoreCode()));
        }

        // Attempt to run a full feed when batch not finished
        if (!$this->getBatchMode() && $this->batchInProgress()) {
            Mage::throwException(sprintf('Batch generation is in progress. Wait for the batch to finish or force this action by removing [%s]', $this->getBatchLockPath()));
        }


        if ($completedForToday = $this->getBatchMode() ? $this->getBatch()->completedForToday() : false) {
            Mage::throwException(sprintf('[%s] Feed Completed for Today %s! Wait till tomorrow or remove lock file: %s', $this->getScheduleId(), date('Y-m-d'), $this->getBatchLockPath()));
        }

        $this->log('START');
        if ($this->getData('verbose')) {
            session_start(); // fix for magento 1.4 complaining abut headers. Not sure why 1.4 initiates the session
            echo "START\n";
        }

        if ($this->getBatchMode() && !$this->getConfigVar('use_batch_segmentation', 'file')) {
            $this->setBatchMode(false); 
        }

        $this->initialize();

        $this->_total_items = null;
        if ($this->getBatchMode()) {
            $this->getBatch()->setData('verbose', $this->getData('verbose'));
            $count_coll = clone $this->_getCollection();
            $count_coll->reset(Zend_Db_Select::GROUP);
            $this->_total_items = $count_coll->getSize();
            $this->getBatch()->setTotalItems($this->_total_items);
            unset($count_coll);
            $batch_limit = ($this->getConfigVar('batch_limit', 'file') == 0 ? 1000 : $this->getConfigVar('batch_limit', 'file'));
            $batch_limit = ($batch_limit <= $this->_total_items ? $batch_limit : $this->_total_items);
            $this->getBatch()->setLimit($batch_limit);

            // Lock cleanup
            $locked = $this->getBatch()->aquireLock();
            if (!$locked && !$completedForToday) {
                if ($this->getData('verbose')) {
                    echo sprintf('WARNING - previous batch did not complete. Clearing lock file %s', $this->getBatchLockPath()) . "/n";
                }
                @unlink($this->getBatchLockPath());
            }

            // Can't get lock, last batch did not finish or another batch is running.
            if (!$locked && !$this->getBatch()->aquireLock()) {
                Mage::throwException(sprintf('Batch generation is locked. Force this action by removing [%s]', $this->getBatchLockPath()));
            }
        }

        $collection = $this->getCollection();
        if (is_null($this->_total_items)) {
            $this->_total_items = $collection->getSize();
        }

        if (!$this->getBatchMode() || ($this->getBatchMode() && $this->getBatch()->getIsNew())) {

            $this->writeFeed($this->getHeader(), false);
            // Clear processes every time but wen batch mode and queue not completed
            $this->getTools()->clearProcess();
        }

        $product_types = $this->getConfig()->getMultipleSelectVar('product_types', $this->getData('store_id'), 'filters');
        $this->_current_iter = 0;
        if ($this->getBatchMode() && !$this->getBatch()->getIsNew()) {
            $this->_current_iter = (int) $this->getBatch()->getOffset() - $this->getBatch()->getLimit();
        }
        else {
            $this->_current_iter = 0;
        }

        Mage::getSingleton('core/resource_iterator')->walk(
            $collection->getSelect(), array(array($this, 'processProductCallback')), array(
                'product_types' => $product_types,
            )
        );

        $this->closeHandle();

        if ($this->getData('verbose')) {
            echo "---------------------------------------------------------------------\n";
        }
        $this->log(sprintf('Finished run. Items: %3d added, %3d skipped of %3d total | feed file %s', $this->getCountProductsExported(), $this->getCountProductsSkipped(), $this->_total_items, $this->getFeedPath()));

        if ($this->getBatchMode()) {
            $this->getBatch()->releaseLock();
        }
        $this->releaseLock();

        $t = round(time()-$time);
        $this->log('END / MEMORY USED: ' . $this->formatMemory(memory_get_usage(true) - $memory). ', TIME SPENT: '. sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60));

        return $this;
    }

    public function getProduct($id) {
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStoreId())
            ->setId($id);

        $product->getResource()->load($product, $id);

        return $product;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @return array
     */
    public function generateProductMap($product) {

        $productMap = $this->getProductMapModel($product)
            ->setColumnsMap($this->getColumnsMap())
            ->setEmptyColumnsReplaceMap($this->getEmptyColumnsReplaceMap())
            ->initialize();

        return $productMap->map();
    }

    /**
     * Build the map model path based on product type
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $args
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
     */
    public function getProductMapModel($product, $args = array())
    {
        $is_assoc = array_key_exists('is_assoc', $args) ? $args['is_assoc'] : false;

        $model_path = 'map_product_'. $product->getTypeId();
        if ($is_assoc) $model_path .= '_associated';

        $model_args = array('store_code' => $this->getData('store_code'),
            'store_id'   => $this->getData('store_id'),
            'website_id' => $this->getData('website_id'),
            'product'    => $product
        );

        $mapModel = null;
        $file_path = Mage::getConfig()->getModuleDir('model', 'RocketWeb_GoogleBaseFeedGenerator');
        $file_path .= 'Map/Product/' . ucfirst(strtolower($product->getTypeId()));

        if (file_exists($file_path)) {
            $mapModel = Mage::getModel('googlebasefeedgenerator/' . $model_path, $model_args);
        }

        if (!$mapModel) {
            $mapModel = Mage::getModel('googlebasefeedgenerator/map_product_abstract', $model_args);
        }

        return $mapModel;
    }

    /**
     * @param $args
     */
    public function processProductCallback($args)
    {
        $row = $args['row'];

        // Skip if product type is not enabled
        if (!$this->isProductTypeEnabled($row['type_id'])) {
            return;
        }

        // Check associated item
        $is_assoc_configurable = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $this->getStoreId());
        if (!$is_assoc_configurable) {
            $is_assoc_configurable = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Configurable::PRODUCT_TYPE_SUBSCTIPTION_CONFIGURABLE, $this->getStoreId());
        }

        $is_assoc_grouped = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $this->getStoreId());
        if (!$is_assoc_grouped) {
            $is_assoc_grouped = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Grouped::PRODUCT_TYPE_SUBSCTIPTION_GROUPED, $this->getStoreId());
        }
        $is_assoc = ($is_assoc_configurable || $is_assoc_grouped);


        // Memorise possible duplicate items and skip current simple product
        if ($is_assoc) {
            if ($this->getTools()->lockDuplicates($row, $is_assoc_configurable, $is_assoc_grouped) && !$this->getTestMode()) {
                return;
            }
        }

        // Prepare product and map object
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStoreId())
            ->load($row['entity_id']);

        $productMap = $this->getProductMapModel($product, array('is_assoc' => $is_assoc))
            ->setColumnsMap($this->getColumnsMap())
            ->setEmptyColumnsReplaceMap($this->getEmptyColumnsReplaceMap())
            ->initialize();

        $this->_current_iter++;

        if ($productMap->checkSkipSubmission()) {
            return;
        }

        $this->addProductToFeed($productMap);

        // Free up memory
        $this->getTools()->clearNestedObject($product);
        $this->getTools()->unsConfigurableAttributesAsArray($product);
        unset($product, $productMap, $row);

        if ($this->getData('verbose')) {
            echo $this->formatMemory(memory_get_usage(true)) . " - SKU " . $args['row']['sku'] . ", ID " . $args['row']['entity_id'] . "\n";
        }

        if ($this->_current_iter % $this->getLogCountStep($this->_total_items) == 0) {
            $this->log(sprintf("(%3d, %3d) products (processed, max)", $this->_current_iter, $this->_total_items));
        }
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getCollection()
    {
        if (is_null($this->_collection)) {
            $this->_collection = clone $this->_getCollection();
            if ($this->getBatchMode()) {
                $this->_collection->getSelect()->limit($this->getBatch()->getLimit(), $this->getBatch()->getOffset() - $this->getBatch()->getLimit());
            } elseif ($this->getTestMode()) {
                if ($this->getTestSku()) {
                    $this->_collection->addAttributeToFilter(
                        array(
                        array('attribute' => 'sku', 'eq' => $this->getTestSku()),
                        array('attribute' => 'entity_id', 'eq' => $this->getTestSku())
                        )
                    );
                    //$this->_collection->addAttributeToFilter('sku', $this->getTestSku());
                } elseif ($this->getTestOffset() >= 0 && $this->getTestLimit() > 0) {
                    $this->_collection->getSelect()->limit(($this->getTestLimit() > 0 ? $this->getTestLimit() : 0), ($this->getTestOffset() > 0 ? $this->getTestOffset() : 0));
                } else {
                    Mage::throwException(sprintf("Invalid parameters for test mode: sku %s or offset %s and limit %s", $this->getTestSku(), $this->getTestOffset(), $this->getTestLimit()));
                }
            }
        }
        return $this->_collection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStore($this->getData('store_code'))
            ->addStoreFilter($this->getData('store_code'));

        $this->addProductTypeToFilter($collection);

        // Filter visible / enabled products
        $collection->addAttributeToFilter('status', array('neq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED))
            ->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $included_categories = $this->getConfig()->getIncludedCategoryIds($this->getStoreId());
        if ($included_categories) {
            $this->_addCategoriesToFilter($collection, $included_categories);
        }

        $attribute_sets = $this->getConfig()->getAttributeSets($this->getStoreId());
        if ($attribute_sets) {
            $collection->addAttributeToFilter('attribute_set_id', $attribute_sets);
        }

        if (!$this->getConfigVar('add_out_of_stock', 'filters')) {
            $collection->addPriceData(null, $this->getData('website_id'));
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        }

        if (!$this->getTestMode() && $this->getConfigVar('sku', 'debug') != "") {
            $collection->addAttributeToFilter('sku', $this->getConfigVar('sku', 'debug'));
        }

        if ($included_categories) {
            $collection->getSelect()->group('e.entity_id');
        }

        return $collection;
    }

    /**
     * Adds category ids to collection filter, adding join to category-product table if needed
     *
     * @param $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     * @param $category_ids int[]
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _addCategoriesToFilter($collection, $category_ids)
    {
        $conditions = array(
            'cat_index.product_id=e.entity_id',
            $collection->getConnection()->quoteInto('cat_index.category_id IN (' . implode(',', $category_ids) . ')', "")
        );
        $conditions[] = $collection->getConnection()->quoteInto('cat_index.store_id=?', $this->getData('store_id'));
        $joinCond = join(' AND ', $conditions);
        $fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);

        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        } else {
            $collection->getSelect()->join(
                array('cat_index' => $collection->getTable('catalog/category_product_index')), $joinCond, array('cat_index_category_id' => 'category_id')
            );
        }

        return $collection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function addProductTypeToFilter($collection)
    {
        $default_product_types = array(
            Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
        );
        $product_types = $this->_getProductTypes();

        $not_in_product_types = array_diff($default_product_types, $product_types);
        $in_product_types = array_diff($product_types, $default_product_types);

        if (count($in_product_types)) {
            $collection->addAttributeToFilter('type_id', array('in' => $product_types));
        }

        if (count($not_in_product_types) > 0) {
            $collection->addAttributeToFilter('type_id', array('nin' => $not_in_product_types));
        }

        return $collection;
    }

    /**
     * Returns columns map in asc order.
     * Skips columns with attributes that doesn't exist.
     * Caches eav attributes model used.
     *
     *  [column] =>
     *            [column]
     *            [attribute code or directive code]
     *            [default_value]
     *            [order]
     *
     * @return array
     */
    public function getColumnsMap()
    {
        if (!is_null($this->_columns_map)) {
            return $this->_columns_map; 
        }

        $tmp = $cfg_map = $this->getConfigVar('map_product_columns', 'columns');
        foreach ($tmp as $k => $arr) {
            if (!$this->getConfig()->isDirective($arr['attribute'], $this->getData('store_id'))) {
                $attribute = $this->getAttribute($arr['attribute']);
                if ($attribute == false) {
                    $this->log(sprintf("Column '%s' ignored, can't find attribute with code '%s'.", $arr['column'], $arr['attribute']), Zend_Log::WARN);
                    unset($cfg_map[$k]);
                    continue;
                }
                $attribute->setStoreId($this->getData('store_id'));
                $this->setAttribute($attribute);
            }
        }
        $this->_columns_map = array();
        $output_columns = $this->getOutputColumns();
        foreach ($cfg_map as $arr) {
            if (empty($output_columns) || in_array($arr['column'], $output_columns)) {
                $this->_columns_map[$arr['column']] = $arr;
            }
        }

        // Check shipping enabled and if set shipping column.
        if (isset($this->_columns_map['shipping']) && $this->_columns_map['shipping']['attribute'] == "rw_gbase_directive_shipping" && !$this->getConfigVar('enabled', 'shipping')) {
            unset($this->_columns_map['shipping']);
        }

        /*$names = array(($this->getConfigVar('locale', 'file') == 'en_GB') ? 'colour' : 'color', 'size', 'gender', 'age_group', 'material', 'pattern');
        foreach ($names as $n) {
            // Check and load apparel attributes.
            if (isset($this->_columns_map[$n]) && isset($this->_columns_map[$n]['attribute']) && $this->_columns_map[$n]['attribute'] == 'rw_gbase_directive_apparel_' . $n) {
                if (!$this->loadApparelAttributes($n) && (isset($this->_columns_map[$n]['defailt_value']) && $this->_columns_map[$n]['default_value'] == "")) {
                    $this->log(sprintf("Column '%s' ignored, can't find any attributes assigned.", $this->_columns_map[$n]['column']), Zend_Log::WARN);
                    unset($this->_columns_map[$n]);
                }
            }
        }*/

        // Check attribute assigned to availability column (stock status).
        if (!$this->getConfigVar('use_default_stock', 'columns') && isset($this->_columns_map['availability']) && $this->getConfigVar('stock_attribute_code', 'columns') !== "") {
            $attribute = $this->getAttribute($this->getConfigVar('stock_attribute_code', 'columns'));
            if ($attribute !== false) {
                $attribute->setStoreId($this->getData('store_id'));
                $this->setAttribute($attribute);
            } else {
                $this->log(sprintf("Column '%s' ignored, can't find attribute with code '%s'.", $this->_columns_map['availability']['column'], $this->getConfigVar('stock_attribute_code', 'columns')), Zend_Log::WARN);
                unset($this->_columns_map['availability']);
            }
        }

        $s = array();
        foreach ($this->_columns_map as $column => $arr) {
            $s[$column] = $arr['order'];
        }
        array_multisort($s, $this->_columns_map);

        return $this->_columns_map;
    }

    /**
     * @deprecated
     *
     * @param string $name
     * @return bool
     */
    protected function loadApparelAttributes($name)
    {
        $one = false;

        if ($name != "material" && $name != "pattern") {
            $attributes_codes = $this->getConfig()->getMultipleSelectVar($name . '_attribute_code', $this->getData('store_id'), 'apparel');
            if (count($attributes_codes) > 0) {
                foreach ($attributes_codes as $attr_code) {
                    $attribute = $this->getAttribute($attr_code);
                    if ($attribute !== false) {
                        $attribute->setStoreId($this->getData('store_id'));
                        $this->setAttribute($attribute);
                        $one = true;
                    }
                }
            }
        }

        if ($name != "gender" && $name != "age_group") {
            $attributes_codes = $this->getConfig()->getMultipleSelectVar('variant_' . $name . '_attribute_code', $this->getData('store_id'), 'apparel');
            if (count($attributes_codes) > 0) {
                foreach ($attributes_codes as $attr_code) {
                    $attribute = $this->getAttribute($attr_code);
                    if ($attribute !== false) {
                        $attribute->setStoreId($this->getData('store_id'));
                        $this->setAttribute($attribute);
                        $one = true;


                    }
                }
            }
        }

        return $one;
    }

    /**
     * Returns columns map replaced by other attributes when it's value is empty for a product.
     * Sorts result asc by rule order.
     * Caches eav attributes model used.
     * Skips rules with attributes that doesn't exist.
     *
     * @return array
     */
    protected function getEmptyColumnsReplaceMap()
    {
        if (!is_null($this->_empty_columns_replace_map)) {
            return $this->_empty_columns_replace_map;
        }

        $_columns_map = $this->getColumnsMap();
        $tmp = $cfg_map = $this->getConfigVar('map_replace_empty_columns', 'filters');

        if (empty($cfg_map)) {
            $tmp = $cfg_map = array();
        }

        foreach ($tmp as $k => $arr) {

            if (!isset($_columns_map[$arr['column']])) {
                unset($cfg_map[$k]);
                continue;
            }

            if (strpos($arr['attribute'], 'rw_gbase_directive_') === false) {
                $attribute = $this->getAttribute($arr['attribute']);
                if ($attribute == false && empty($arr['static'])) {
                    $this->log(sprintf("Rule ('%s', '%s', '%d') is ignored, can't find attribute with code '%s'.", $arr['column'], $arr['attribute'], @$arr['order'], $arr['attribute']), Zend_Log::WARN);
                    unset($cfg_map[$k]);
                    continue;
                } elseif ($attribute) {
                    $attribute->setStoreId($this->getData('store_id'));
                    $this->setAttribute($attribute);
                }
            }
        }

        $this->_empty_columns_replace_map = $cfg_map;

        // Move rules without order to the bottom.
        $s = array();
        foreach ($this->_empty_columns_replace_map as $k => $arr) {
            if (!isset($arr['order']) || (isset($arr['order']) && $arr['order'] == "")) {
                $this->_empty_columns_replace_map[$k]['order'] = 99999;
            }

            $s[$k] = $arr['order'];
        }
        array_multisort($s, $this->_empty_columns_replace_map);

        return $this->_empty_columns_replace_map;
    }

    /**
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Generator
     */
    protected function loadAdditionalAttributes()
    {

        $codes = array('status');
        foreach ($codes as $attribute_code) {
            $this->setAttribute($this->getAttribute($attribute_code));
        }

        return $this;
    }

    public function getHeader()
    {
        return array_combine(array_keys($this->_columns_map), array_keys($this->_columns_map));
    }

    protected function writeFeed($fields, $add_new_line = true)
    {
        // google error: "Too many column delimiters"
        foreach ($this->_columns_map as $column => $arr) {
            if (isset($fields[$column]) && $fields[$column] == "") {
                $fields[$column] = " "; 
            }
        }
        fwrite($this->getHandle(), ($add_new_line ? PHP_EOL : '') . implode("\t", $fields));
    }

    /**
     * @param  RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract $productMap
     */
    protected function addProductToFeed($productMap)
    {
        try {
            $rows = $productMap->map();

            if ($productMap->isSkip()) {
                return $this;
            }

            foreach ($rows as $row) {
                // format prices
                foreach ($row as $column => $value) {
                    if (($column == "price" || $column == "sale_price") && trim($value) != "") {
                        $row[$column] = $this->formatPrice($value);
                    }
                }

                $this->writeFeed($row);
                $this->_count_products_exported++;
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), Zend_Log::ERR);
            if ($this->getTestMode()) {
                if ($productMap instanceof RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract && $productMap->getProduct() instanceof Mage_Catalog_Model_Product) {
                    Mage::throwException(sprintf("product id %d product sku %s: " . $e->getMessage(), $productMap->getProduct()->getId(), $productMap->getProduct()->getSku()));
                } else {
                    Mage::throwException($e->getMessage());
                }
            }
        }

        return $this;
    }

    /**
     * @param $value
     * @param null  $format_prices_locale
     * @return string
     */
    public function formatPrice($value, $format_prices_locale = null)
    {
        if (trim($value) == "") {
            return $value; 
        }

        if (is_null($format_prices_locale)) {
            $format_prices_locale = $this->getConfigVar('format_prices_locale', 'columns');
        }

        if (!is_numeric($value)) {
            // no vars or objects references in function getNumber
            $value = Mage::app()->getLocale()->getNumber($value);
        } elseif (is_string($value)) {
            $value = floatval($value);
        }

        $base_currency_code = Mage::getStoreConfig('currency/options/base', $this->getStoreId());
        $to_currency_code = Mage::app()->getStore($this->getStoreId())->getCurrentCurrencyCode();
        if ($base_currency_code != $to_currency_code) {
            if (is_null($this->_currencyRate)) {
                $this->_currencyRate = Mage::app()->getStore()->getCurrentCurrency()
                    ->getCurrencyRates(
                        $base_currency_code,
                        $to_currency_code
                    );

                if (!(is_array($this->_currencyRate) && isset($this->_currencyRate[$to_currency_code]))) {
                    Mage::throwException(sprintf('Can\'t find currency rate %s to %s', $base_currency_code, $to_currency_code));
                }
            }
            $value = $this->_currencyRate[$to_currency_code] * $value;
        }

        if (is_null($this->_currencyObject)) {
            $locale = Mage::getStoreConfig('general/locale/code', $this->getStoreId());
            if (!$format_prices_locale) {
                $locale = "en_US";
            }
            $this->_currencyObject = new Zend_Currency($to_currency_code, $locale);
        }

        $options = array(
            'display' => Zend_Currency::NO_SYMBOL,
        );
        $value = sprintf("%.2F", $value);
        $value = $this->_currencyObject->toCurrency($value, $options);

        return $value . ' ' . $this->getData('store_currency_code');
    }

    /**
     * Gets feed's filepath.
     *
     * @return string
     */
    public function getFeedPath()
    {
        $filepath = rtrim(Mage::getBaseDir(), DS) . DS . rtrim($this->getConfigVar('feed_dir', 'file'), DS) . DS;

        if (!$this->getTestMode()) {
            $name = sprintf($this->getConfigVar('feed_filename', 'file'), $this->getData('store_code'));
        } else {
            $name = sprintf($this->getConfigVar('test_feed_filename', 'file'), $this->getData('store_code'));
        }

        return $filepath . $name;
    }


    protected function initSavePath()
    {
        $path = dirname($this->getFeedPath());
        $ioAdapter = new Varien_Io_File();
        if (!is_dir($path)) {
            $ioAdapter->mkdir($path);
            if (!is_dir($path)) {
                Mage::throwException(sprintf('Not enough permissions, can\'t create dir [%s].', $path));
            }
        }
    }

    /**
     * @return bool|null|resource
     */
    protected function getHandle()
    {
        if ($this->_handle === null) {
            $mode = "a";
            if (!$this->getBatchMode() || ($this->getBatchMode() && $this->getBatch()->getIsNew())) {
                $mode = "w"; 
            }

            $this->_handle = @fopen($this->getFeedPath(), $mode);
            if ($this->_handle === false) {
                Mage::throwException(sprintf('Not enough permissions to write to file %s.', $this->getFeedPath()));
            }
        }

        return $this->_handle;
    }

    protected function closeHandle()
    {
        @fclose($this->_handle);
    }

    public function getCountProductsExported()
    {
        return $this->_count_products_exported;
    }

    public function getCountProductsSkipped()
    {
        return $this->_count_products_skipped;
    }

    /**
     * Could take negative value to decrease count
     * @param $val
     * @return $this
     */
    public function updateCountSkip($val = 1)
    {
        $this->_count_products_skipped = $this->_count_products_skipped + $val;
        return $this;
    }

    protected function getLogCountStep($total)
    {
        $step = 1000;
        if ($total <= 0) { return $step; 
        }
        if ($total >= 50000) { $step = 2500; 
        }
        elseif ($total >= 10000) $step = 1000;
        elseif ($total >= 1000) $step = 100;
        elseif ($total >= 500) $step = 50;
        elseif ($total <= 500 && $step > 10) $step = 10;
        else { $step = 1; 
        }

        return $step;
    }

    /**
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('googlebasefeedgenerator/config');
    }

    /**
     * @param string $key
     * @param string $section
     * @return mixed
     */
    public function getConfigVar($key, $section)
    {
        return $this->getConfig()->getConfigVar($key, $this->getData('store_id'), $section);
    }

    /**
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Tools
     */
    public function getTools()
    {
        return Mage::getSingleton('googlebasefeedgenerator/tools')->setConfig($this->getConfig())->setData('store_id', $this->getData('store_id'));
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function _getLog()
    {
        return Mage::getSingleton('googlebasefeedgenerator/log');
    }

    /**
     * @param $msg
     * @param null $level
     * @param null $writer
     */
    public function log($msg, $level = null, $writer = null)
    {
        if (is_null($level)) {
            $level = Zend_Log::INFO; 
        }
        if (!$this->hasData('force_log')) {
            $this->setData('force_log', false);
            if ($this->getConfigVar('force_log', 'file')) {
                $this->setData('force_log', true); 
            }
        }

        if (!$this->hasData('log_filename')) {
            $this->setData('log_filename', sprintf($this->getConfigVar('log_filename', 'file'), $this->getData('store_code')));
        }

        if ($this->getBatchMode()) {
            $msg = sprintf('[%s] ' . $msg, $this->getBatch()->getScheduleId());
        }

        $m = memory_get_usage();
        $msg = sprintf('(mem %s) ', $this->formatMemory($m)) . $msg;

        $options = array(
            'file' => $this->getData('log_filename'),
            'force' => $this->getData('force_log'),
        );
        $this->_getLog()->log($msg, $level, $writer, $options);

        if (!$this->getBatchMode()) {
            $this->_getLog()->log($msg, $level, RocketWeb_GoogleBaseFeedGenerator_Model_Log::WRITER_MEMORY);
        }

        if ($this->getData('verbose')) {
            echo $msg . "\n";
        }

        unset($msg, $level, $writer, $m, $mem, $options);
    }

    /**
     * @param $memory
     * @return string
     */
    public function formatMemory($memory)
    {
        $units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
        $m = @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2) . ' ' . $units[$i];
        return sprintf('%4.2f %s', $m, $units[$i]);
    }

    /**
     * Wrapper for attribute cache in Tools object.
     *
     * @param  $code
     * @return mixed|null
     */
    public function getAttribute($attributeCode)
    {
        return $this->getTools()->getAttribute($attributeCode);
    }

    /**
     * Wrapper for set attribute cache in Tools object
     *
     * @param $attribute
     */
    public function setAttribute($attribute)
    {
        return $this->getTools()->setAttribute($attribute);
    }


    public function __destruct()
    {
        @fclose($this->_storeLockFile);
    }

    /**
     * @return string
     */
    public function getLockPath()
    {
        return rtrim(dirname($this->getFeedPath()), DS) . DS . sprintf($this->getConfigVar('store_lock_filename', 'file'), $this->getStoreCode());
    }

    /**
     * @return string
     */
    public function getBatchLockPath()
    {
        return rtrim(dirname($this->getFeedPath()), DS) . DS . sprintf($this->getConfigVar('batch_lock_filename', 'file'), $this->getStoreCode());
    }

    /**
     * Implements the lock feed generation by store using the file system lock mechanism.
     * @return bool
     */
    public function acquireLock()
    {
        // Acquire an exclusive lock on file without blocking the script
        if (!flock($this->_storeLockFile, LOCK_EX | LOCK_NB)) {
            $this->log(sprintf('Can\'t acquire feed lock for store [%s]', $this->getStoreCode()) . ($this->hasScheduleId() ? sprintf('script [%s]', $this->getScheduleId()) : ''), Zend_Log::ERR);
            $this->log(sprintf('Ensure write proper write permissions to [%s]', $this->getLockPath()));
            return false;
        }

        ftruncate($this->_storeLockFile, 0); // truncate file
        fwrite($this->_storeLockFile, date("Y-m-d H:i:s\n"));
        fflush($this->_storeLockFile); // flush output before releasing the lock

        return true;
    }

    /**
     * Release the file lock
     * @return $this
     */
    public function releaseLock()
    {
        // Releasing the lock will also be done automatically when php runtime ends
        flock($this->_storeLockFile, LOCK_UN);
        return $this;
    }

    public function batchInProgress()
    {
        if ($mixed = @file_get_contents($this->getBatchLockPath())) {
            $mixed = @unserialize($mixed);
            if (is_array($mixed) && (int)$mixed['offset'] < (int)$mixed['total'] - (int)$mixed['limit']) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    protected function _getProductTypes()
    {
        $product_types = $this->getConfigVar('product_types', 'filters');
        return explode(",", $product_types);
    }

    /**
     * @param $type
     * @return bool
     */
    public function isProductTypeEnabled($type)
    {
        return in_array($type, $this->_getProductTypes());
    }
}
