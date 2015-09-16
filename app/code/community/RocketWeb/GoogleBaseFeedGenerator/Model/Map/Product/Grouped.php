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
 */
class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Grouped extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
{
    /**
     * Support for configurable items product option not yet implemented
     * @return bool
     */
    protected function _isAllowProductOptions()
    {
        return false;
    }

    public function getChildrenCount() {
        return (count($this->getAssocIds()));
    }

    /**
     * Iterate through associated products and set mapping objects
     *
     * @return $this
     */
    public function _beforeMap()
    {
        $this->_assocs = array();
        foreach ($this->getAssocIds() as $assocId) {

            $assoc = Mage::getModel('catalog/product');
            $assoc->setStoreId($this->getStoreId());
            $assoc->getResource()->load($assoc, $assocId);
            $assoc->setData('quantity', 0);

            if ($this->getGenerator()->getData('verbose')) {
                echo $this->getGenerator()->formatMemory(memory_get_usage(true)) . " - Grouped associated SKU " . $assoc->getSku() . ", ID " . $assoc->getEntityId() . "\n";
            }

            $stock = $this->getConfig()->getOutOfStockStatus();

            if (!$this->getConfigVar('use_default_stock', 'columns')) {
                $stock_attribute = $this->getGenerator()->getAttribute($this->getConfigVar('stock_attribute_code', 'columns'));
                if ($stock_attribute === false) {
                    Mage::throwException(sprintf('Invalid attribute for Availability column. Please make sure proper attribute is set under the setting "Alternate Stock/Availability Attribute.". Provided attribute code \'%s\' could not be found.', $this->getConfigVar('stock_attribute_code', 'columns')));
                }

                $stock = trim(strtolower($this->getAttributeValue($assoc, $stock_attribute)));
                if (array_search($stock, $this->getConfig()->getAllowedStockStatuses()) === false) {
                    $stock = $this->getConfig()->getOutOfStockStatus();
                }
            } else {
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                $stockItem->setStoreId($this->getStoreId());
                $stockItem->getResource()->loadByProductId($stockItem, $assoc->getId());
                $stockItem->setOrigData();

                if ($stockItem->getId() && $stockItem->getIsInStock()) {
                    $assoc->setData('quantity', $stockItem->getQty());
                    $stock = $this->getConfig()->getInStockStatus();
                }

                // Clear stockItem memory
                unset($stockItem->_data);
                $this->getTools()->clearNestedObject($stockItem);
            }

            // Append assoc considering the appropriate stock status
            if ($this->getConfigVar('add_out_of_stock', 'grouped_products') || $stock == $this->getConfig()->getInStockStatus()) {
                $this->_assocs[$assocId] = $assoc;
            } else {
                // Set skip messages
                if ($this->getConfigVar('log_skip')) {
                    $this->log(sprintf("product id %d sku %s, grouped associated, skipped - out of stock", $assocId, $assoc->getSku()));
                }
            }
        }

        return parent::_beforeMap();
    }

    /**
     * Builds the associated maps from assocs array
     *
     * @return $this
     */
    protected function _setAssocMaps()
    {
        $assocMapArr = array();
        foreach ($this->getProduct()->getTypeInstance()->getAssociatedProducts() as $assoc) {
            $assocMap = $this->_getAssocMapModel($assoc);
            $assocId = $assoc->getEntityId();
            if (!in_array($assocId, $this->_assoc_ids) || $assocMap->checkSkipSubmission(true, 'grouped')) {
                unset($this->_assocs[$assocId]);
                continue;
            }
            $assocMapArr[$assocId] = $assocMap;
        }
        $this->setAssocMaps($assocMapArr);

        if (count($assocMapArr) <= 0) {
            $this->setSkip(sprintf("product id %d product sku %s, skipped - All associated products of the grouped product are disabled or out of stock.", $this->getProduct()->getId(), $this->getProduct()->getSku()));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function map()
    {
        $rows = array();
        $this->_beforeMap();

        if ($this->getConfig()->isAllowGroupedMode($this->getStoreId())) {
            if (!$this->isSkip()) {

                // simulate parent::map() without clearing associated_maps from memory, as associated more could be on.
                $row = parent::_map();
                reset($row); $row = current($row);
                $this->_checkEmptyColumns($row);

                if (!$this->isSkip()) {
                    $rows[] = $row;
                }
            }
        }

        if ($this->getConfig()->isAllowGroupedAssociatedMode($this->getStoreId())) {
            foreach ($this->getAssocMaps() as $assocMap) {

                $row = $assocMap->map();
                reset($row); $row = current($row);

                if (!$assocMap->isSkip()) {
                    $rows[] = $row;
                }
            }
        }

        // if any of the associated not skipped, force add them to the feed
        if (count($rows)) {
            $this->unSkip();
        }

        return $this->_afterMap($rows);
    }

    /**
     * @param $rows
     * @return array
     */
    public function _afterMap($rows)
    {
        // Free some memory
        foreach ($this->_assocs as $assoc) {
            $this->getTools()->clearNestedObject($assoc);
        }
        return parent::_afterMap($rows);
    }

    /**
     * @return float
     */
    public function getPrice($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (Mage::helper('googlebasefeedgenerator')->isModuleEnabled('Aitoc_Aitcbp')) {
            $product = $product->load($product->getid());
        }

        $price = $this->calcMinimalPrice($product);
        if ($price <= 0) {
            $price = $product->getPrice();
        }

        return $price;
    }

    /**
     * @param $product
     * @return float|mixed
     */
    public function calcMinimalPrice($product, $params = array('column' => 'price'))
    {
        $assocMapArr = $this->getAssocMaps();
        $price = 0.0;
        $display_min = $this->getConfigVar('price_display_mode', 'grouped_products');

        if ($display_min == RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Price::PRICE_SUM_DEFAULT_QTY) {
            $price = 0.0;
            foreach ($assocMapArr as $assocMap) {
                $qty = $assocMap->getProduct()->getQty();
                $qty = $qty > 0 ? $qty : 1;
                $assoc_price = $assocMap->mapColumn($params['column']) * $qty;
                $price += $assoc_price;

            }
        } else {
            // RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Price::PRICE_START_AT
            $minAssocMap = $this->findMinimalPriceProduct($assocMapArr);
            if ($minAssocMap === false) {
                return $price;
            }
            $price = $minAssocMap->mapColumn($params['column']);

        }

        return $price;
    }

    /**
     * @return float
     */
    public function getSpecialPrice($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $price = $this->calcMinimalPrice($product, array('column' => 'sale_price'));
        if ($price <= 0) {
            $price = '';
        }

        return $price;
    }

    public function hasSpecialPrice($product, $special_price, $rules = true)
    {
        $product = $this->getProduct();
        $special_price = $this->calcMinimalPrice($product);
        if ($special_price <= 0) {
            return false;
        }

        $has = false;
        $assocMapArr = $this->getAssocMaps();
        $has_default_qty = $this->_hasDefaultQty($assocMapArr);
        $display_min = $this->getConfigVar('price_display_mode', 'grouped_products');

        if ($has_default_qty && $display_min == RocketWeb_GoogleBaseFeedGenerator_Model_Source_Pricegroupedmode::PRICE_SUM_DEFAULT_QTY) {
            foreach ($assocMapArr as $assocMap) {
                $associatedProduct = $assocMap->getProduct();
                if ($associatedProduct->getQty() > 0) {
                    if ($assocMap->hasSpecialPrice($associatedProduct, $assocMap->getSpecialPrice($associatedProduct))) {
                        $has = true;
                        break;
                    }
                }
            }
        } else {
            // RocketWeb_GoogleBaseFeedGenerator_Model_Source_Pricegroupedmode::PRICE_START_AT
            $minAssocMap = $this->findMinimalPriceProduct($assocMapArr);
            if ($minAssocMap === false) {
                return false;
            }
            $associatedProduct = $minAssocMap->getProduct();
            if ($minAssocMap->hasSpecialPrice($associatedProduct, $minAssocMap->getSpecialPrice($associatedProduct))) {
                $has = true;
            }
        }

        return $has;
    }

    /**
     * @param array $params
     * @return string
     * TODO: cache the output as the method is called many times from Shipping::collectRates()
     */
    protected function mapDirectivePrice($params = array())
    {
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        $product = $this->getProduct();

        // Try to get the price from cache, fix price by option
        if (!$price = $this->getCacheAssociatedPrice($product->getId())) {
            $price = $this->getPrice($product);
        }

        return $this->cleanField($price, $params);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveSalePrice($params = array())
    {
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        $product = $this->getProduct();

        $price = $this->getSpecialPrice($product);
        if ($price <= 0 ) {
            $price = '';
        }
        return $this->cleanField($price, $params);
    }

    protected function mapDirectiveSalePriceEffectiveDate($params = array())
    {
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        $product = $this->getProduct();
        $assocMapArr = $this->getAssocMaps();

        if (!$this->hasSpecialPrice($product, $this->getSpecialPrice($product))) {
            return '';
        }

        $display_mode = $this->getConfigVar('price_display_mode', 'grouped_products');
        if ($this->_hasDefaultQty($assocMapArr)
            && $display_mode == RocketWeb_GoogleBaseFeedGenerator_Model_Source_Pricegroupedmode::PRICE_SUM_DEFAULT_QTY
        ) {
            //get min interval from all associated products
            $start = $end = null;
            foreach ($assocMapArr as $assocMap) {
                if ($assocMap->getProduct()->getQty() > 0) {
                    $dates = $assocMap->_getSalePriceEffectiveDates();
                    if (!empty($dates)) {
                        if (empty($start) || $start < $dates['start']) {
                            $start = $dates['start'];
                        }
                        if (empty($end) || $end > $dates['end']) {
                            $end = $dates['end'];
                        }
                    }
                }
            }
            $cell = $this->formatDateInterval(array('start' => $start, 'end' => $end));
        } else {
            $minAssocMap = $this->findMinimalPriceProduct($assocMapArr);
            $cell = $minAssocMap->mapDirectiveSalePriceEffectiveDate($params);
        }

        return $cell;
    }

    /**
     * @param RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Grouped_Associated[] $assocMapArr
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Grouped_Associated
     */
    public function findMinimalPriceProduct($assocMapArr)
    {
        $minAssocMap = false;
        $min_price = PHP_INT_MAX;
        foreach ($assocMapArr as $assocMap) {
            $associatedProduct = $assocMap->getProduct();
            $price = $assocMap->getPrice($associatedProduct);
            if ($assocMap->hasSpecialPrice($associatedProduct, $assocMap->getSpecialPrice($associatedProduct))) {
                $price = $assocMap->getSpecialPrice($associatedProduct);
            }
            if ($min_price > $price) {
                $min_price = $price;
                $minAssocMap = $assocMap;
            }
        }

        return $minAssocMap;
    }

    protected function _hasDefaultQty($assocMapArr)
    {
        $has = false;
        foreach ($assocMapArr as $assocMap) {
            if ($assocMap->getProduct()->getQty() > 0) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveQuantity($params = array())
    {
        $cell = $this->getInventoryCount();

        // If Qty not set at parent item, summarize it from associated items
        if ($params['map']['param'] == RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Product_Quantity::ITEM_SUM_DEFAULT_QTY) {
            $qty = 0;
            foreach ($this->_assocs as $assocId => $assoc) {
                $qty += $assoc->getData('quantity');
            }
            $cell = $qty ? $qty : $cell;
        }

        $cell = sprintf('%d', $cell);
        $this->_findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * Array with associated products ids in current store.
     *
     * @return array
     */
    public function getAssocIds()
    {
        if (is_null($this->_assoc_ids)) {
            $this->_assoc_ids = $this->loadAssocIds($this->getProduct(), $this->getStoreId());
        }
        return $this->_assoc_ids;
    }
}