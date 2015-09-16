<?php

/**
 * Core module for providing common functionality between BraceletBuilder and other related submodules
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_JewelryDesigner_Model_Api_Abstract extends Mage_Core_Model_Abstract
{
    const STATUS_ENABLED            = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;                # 1
    const VISIBILITY_NOT_VISIBLE    = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;    # 1
    const VISIBILITY_IN_CATALOG     = Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG;     # 2
    const VISIBILITY_IN_SEARCH      = Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH;      # 3
    const VISIBILITY_BOTH           = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;           # 4

    /**
     * Attributes that should be skipped over when converting a
     * product's data into a flat array that will be JSON encoded
     *
     * @var array
     */
    protected $_attrsToSkip = array(
        'attribute_set_id',
        'cat_index_position',
    );

    /**
     * Array of product attributes that we specifically want to call
     * @var array
     */
    protected $_attrsToSelect = array(
        'name',
        'sku',
        'weight',
        'price',
        'size',
        'material',
        'stone',
        'theme',
        'color',
        'visibility',
        'image',
        'small_image',
        'thumbnail',
        'designer_canvas',
        'description',
        'short_description',
        'exclude_from_designer',
        'item_type',
        'bead_width',
        'is_dangle_charm',
        'bracelet_has_clip_spots',
    );

    /**
     * Array of product attributes that are either select or multiselect inputs
     * that we want their front-end label value instead of the option id value
     *
     * @var array
     */
    protected $_attrsToFetchLabelsFrom = array(
        'color',
        'stone',
        'material',
        'theme',
        'size',
        'bead_width',
        'item_type'
    );

    /**
     * Array of Category Ids (and their products) that should be excluded
     * from the collection (i.e., 'Essence' line of products)
     *
     * @var array
     */
    protected $_excludedCategoryIds = array();

    /**
     * Type of item (i.e., 'bracelet', 'charm', 'clip', 'spacer')
     *
     * @var string
     */
    protected $_itemType = null;

    /**
     * Instance of Mage::app->getCache()
     */
    protected $_cacheStorage = null;

    protected function _construct()
    {
        parent::_construct();

        // init cacheStorage
        $this->_getCacheStorage();
    }

    /**
     * Get the category's product collection
     *
     * @param   integer     $categoryId # Parent Category Id to pull products from
     * @return  Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection($categoryId, $limit = null, $offset = null)
    {
        $excludedCatIds = $this->getExcludedCategoryIds();

        $collection = Mage::getSingleton('catalog/category')->load($categoryId)
            ->getProductCollection()
            ->distinct(true)
            ->addAttributeToSelect($this->getAttrsToSelect())
            ->addAttributeToFilter('status', self::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', self::VISIBILITY_BOTH)
            ->addAttributeToFilter('designer_canvas', array('notnull' => 1))
            ->addAttributeToFilter('designer_canvas', array('neq' => 'no_selection'))
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'exclude_from_designer', 'null' => 1),
                    array('attribute' => 'exclude_from_designer', 'eq' => 0)
                )
            );


        if (!empty($excludedCatIds) && is_array($excludedCatIds)) {
            /**
             * I'm almost positive 'e' is the alias used for catalog_product_entity,
             * check your query with `echo (string) $collection->getSelect();`
             * if it doesn't work
             */
            $collection->getSelect()->join(
                array('cats' => 'catalog_category_product'),
                'cats.product_id = e.entity_id',
                array() // include an empty array to avoid selecting columns from the join table
            );
            $collection->getSelect()->where('cats.category_id NOT IN (?)', $excludedCatIds);
        }

        // optional limit of collection size
        if (!is_null($limit)){
            if (!is_null($offset)) {
                $collection->getSelect()->limit($limit, $offset);
            } else {
                $collection->getSelect()->limit($limit);
            }
        }


        // Mage::log((string)$collection->getSelect());


        return $collection;
    }

    /**
     * Serves as the main entry point for our API controllers
     * calling our API models but really just wraps the _getProducts()
     * method that can be overwritten in child classes if customization
     * is needed.
     *
     * Returns a an array of arrays for the category's product collection
     *
     * @param  integer  $categoryId     # ID value of the desired category
     * @return string
     */
    public function getProducts($categoryId, $limit = null, $offset = null)
    {
        return $this->_getProducts($categoryId, $limit, $offset);
    }

    /**
     * Returns a an array of arrays for the category's product collection
     *
     * Override this method in the child classes if you need to customize
     *
     * @param  [type] $categoryId [description]
     * @return [type]             [description]
     */
    protected function _getProducts($categoryId, $limit = null, $offset = null)
    {
        $collection = $this->getProductCollection($categoryId, $limit, $offset);
        $parsedData = $this->parseCollection($collection);

        return $parsedData;
    }

    public function getItemType()
    {
        return $this->_itemType;
    }

    public function setItemType($type)
    {
        $this->_itemType = $type;
        return $this;
    }

    /**
     * Wraps the _parseCollection() method that returns a cleaner/well formed
     * array of arrays of product data.
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection   $collection
     * @return array
     */
    public function parseCollection(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $this->_parseCollection($collection);
    }


    /**
     * Returns a cleaner/well formed array of arrays of product data.
     *
     * Override this method in the child classes if you need to customize
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection   $collection
     * @return array
     */
    protected function _parseCollection(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        // default return value
        $collectionToEncode = array();

        $itemType           = $this->getItemType();

        $cacheKey           = 'jewelrydesigner_' . $itemType . '_list';

        if ($this->getCache($cacheKey)) {
            Mage::log('hit ' . $cacheKey . ' cache!!!');
            $cachedData = $this->getCache($cacheKey);
            // Mage::log('cachedData: ' . print_r(unserialize($cachedData), true));

            return unserialize($cachedData);
        }

        if ($collection->count() > 0) {
            $skipAttributes     = $this->getAttrsToSkipInJson();
            $visibilityOptions  = $this->getVisibilityOptions();
            $attrsNeedingLabels = $this->getAttrsToFetchLabelsFrom();

            $baseUrl    = Mage::getBaseUrl();
            $mediaUrl   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
            $baseDir    = Mage::getBaseDir('base');
            $mediaDir   = Mage::getBaseDir('media');

            foreach ($collection as $key => $product) {
                // $product = Mage::getModel('catalog/product')->load($product->getId());

                $productToEncode        = array();
                $data                   = $product->getData();

                $productCatIds          = $product->getCategoryIds();
                $excludedCategoryIds    = $this->getExcludedCategoryIds();

                // check if the product's categories include any of the excluded categories
                $intersect              = array_intersect($excludedCategoryIds, $productCatIds);

                if (empty($intersect)) {
                    // map common product data to flat array
                    foreach ($data as $k => $v) {
                        switch (true) {
                            case (in_array($k, $skipAttributes)):
                                # DO NOTHING: Skip these internal Magento attributes
                                break;

                            // format price as decimal instead of string
                            case ($k === 'price'):
                                $productToEncode['price'] = (float)$v;
                                break;

                            // item_type (i.e., 'bracelet', 'charm', 'clip', 'spacer', etc.)
                            case ($k === 'item_type_value'):
                                // make sure the product's item_type value is present otherwise
                                // default to the value defined in the child classes
                                $productToEncode['item_type'] = (!empty($v)) ? $v : $itemType;
                                break;
                            // product type (i.e., 'configurable', 'simple', etc.)
                            case ($k === 'type_id'):
                                $productToEncode['product_type'] = $v;
                                break;
                            // product's id
                            case ($k === 'entity_id'):
                                $productToEncode['id'] = $v;
                                break;
                            // stock related (is_in_stock, etc.)
                            case ($k === 'stock_item'):
                                $stockData = $v->getData();
                                $productToEncode['is_in_stock'] = (bool)$stockData['is_in_stock'];
                                break;
                            // visibility
                            case ($k === 'visibility'):
                                $productToEncode[$k] = $visibilityOptions[$v];
                                break;
                            // various select/multi-select attributes
                            case (in_array($k, $attrsNeedingLabels)):
                                $productToEncode[$k] = $this->getAttrOptionLabel($k, $v);
                                break;
                            // images
                            case (in_array($k, array('image', 'small_image', 'thumbnail', 'designer_canvas', 'designer_grid_thumb'))):
                                $imgPath = $product->getData($k);

                                if (!empty($imgPath)) {
                                    // $imgUrl     = (string) $imgHelper->init($product, $k)->resize($resize);

                                    $imgUrl     = $this->_resizeImage($product, $k, $itemType);

                                    $imgRelUrl  = str_replace($mediaUrl, '/media/', $imgUrl);
                                    $imgPath    = $baseDir . $imgRelUrl;
                                    if (file_exists($imgPath)) {
                                        /**
                                         * I know, I know...error suppression is usually bad, but the getimagesize()
                                         * will only produce E_WARNING (if accessing file is impossible) or
                                         * E_NOTICE for a read error
                                         *
                                         * @see  http://php.net/manual/en/function.getimagesize.php
                                         */
                                        list($imgWidth, $imgHeight) = @getimagesize($imgPath);

                                        $productToEncode['images'][$k]['url']       = $imgUrl;
                                        $productToEncode['images'][$k]['rel_url']   = $imgRelUrl;
                                        $productToEncode['images'][$k]['filepath']  = $imgPath;

                                        $productToEncode['images'][$k]['width']     = ($data['is_dangle_charm'])
                                            ? round($imgWidth * 0.5)
                                            : $imgWidth;

                                        $productToEncode['images'][$k]['height']    = $imgHeight;

                                        $productToEncode['images'][$k]['regX']      = $imgWidth / 2;
                                        $productToEncode['images'][$k]['regY']      = ($data['is_dangle_charm'])
                                            ? round($imgHeight * 0.12)
                                            : round($imgHeight / 2);
                                    } else {
                                        $productToEncode['images'][$k]['url']       = null;
                                        $productToEncode['images'][$k]['rel_url']   = null;
                                        $productToEncode['images'][$k]['filepath']  = null;
                                        $productToEncode['images'][$k]['width']     = null;
                                        $productToEncode['images'][$k]['height']    = null;
                                        $productToEncode['images'][$k]['regX']      = null;
                                        $productToEncode['images'][$k]['regY']      = null;
                                    }
                                } else {
                                    $productToEncode['images'][$k]['url']       = null;
                                    $productToEncode['images'][$k]['rel_url']   = null;
                                    $productToEncode['images'][$k]['filepath']  = null;
                                    $productToEncode['images'][$k]['width']     = null;
                                    $productToEncode['images'][$k]['height']    = null;
                                    $productToEncode['images'][$k]['regX']      = null;
                                    $productToEncode['images'][$k]['regY']      = null;
                                }
                                break;
                            // boolean values
                            case (in_array($k, array('status', 'is_salable', 'exclude_from_designer', 'bracelet_has_clip_spots', 'is_dangle_charm'))):
                                $productToEncode[$k] = (bool)$v;
                                break;

                            default:
                                $productToEncode[$k] = $v;
                                break;
                        }
                    }

                    // make sure the product's item_type value is present otherwise
                    // default to the value defined in the child classes
                    if (!array_key_exists('item_type', $productToEncode) ||
                        (is_null($productToEncode['item_type']) || $productToEncode['item_type'] == '')) {
                        $productToEncode['item_type'] = $itemType;
                    }

                    // add categories
                    foreach ($productCatIds as $catId) {
                        $category = Mage::getSingleton('catalog/category')->load($catId);
                        $productToEncode['category_ids'][]  = $catId;
                        $productToEncode['categories'][]    = $category->getData('name');
                    }

                    $attributeOptions       = array();
                    $optionsIncreasePrice   = false;

                    // Collect options applicable to the configurable product
                    if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {

                        $prodAttrOpts = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                        foreach ($prodAttrOpts as $prodAttr) {
                            $option = array();

                            $attrId     = $prodAttr->getProductAttribute()->getAttributeId();
                            $attrCode   = $prodAttr->getProductAttribute()->getAttributeCode();
                            $prices     = $prodAttr->getData('prices');

                            $option[$attrId]['attribute_code']    = $attrCode;
                            $option[$attrId]['attribute_label']   = $prodAttr->getData('label');

                            foreach ($prices as $key => $opt) {
                               $option[$attrId]['values'][$opt['value_index']]['product_super_attribute_id'] = $opt['product_super_attribute_id'];
                               $option[$attrId]['values'][$opt['value_index']]['value_index']    = $opt['value_index'];
                               $option[$attrId]['values'][$opt['value_index']]['label']          = $opt['label'];
                               $option[$attrId]['values'][$opt['value_index']]['store_label']    = $opt['store_label'];
                               $option[$attrId]['values'][$opt['value_index']]['pricing_value']  = $opt['pricing_value'];
                            }

                            $attributeOptions[] = $option;
                        }

                        foreach($attributeOptions as $key => $attrConfig) {
                            foreach ($attrConfig[$attrId]['values'] as $valueId => $valueConfig) {
                                // check if the pricing_value is "truthy" (i.e., not null, blank, false, or 0)
                                // if it is, then we'll consider it as an option that increases the product's price
                                if (!empty($valueConfig['pricing_value'])) {
                                    $optionsIncreasePrice = true;

                                    // break out of the foreach loops on the
                                    // first instance of a pricing_value not
                                    // being a blank, null, false or 0 value
                                    break 2;
                                }
                            }
                        }

                        $productToEncode['options_increase_price']  = $optionsIncreasePrice;
                        $productToEncode['attribute_options']       = $attributeOptions;
                    }


                    // add the product to the array of products to encode
                    $collectionToEncode[] = $productToEncode;
                }
            }
        }

        // save the collectionToEncode to Magento's cache
        if (!$this->getCache($cacheKey)) {
            Mage::log('creating cache for $cacheKey: ' . $cacheKey);
            $cache = $this->_getCacheStorage();

            // $cache->save($data, $cacheKey, $tags = array(), $lifeTime = null);
            $cache->save(serialize($collectionToEncode), $cacheKey, array('jewelrydesigner_cache', Mage_Core_Model_Resource_Db_Collection_Abstract::CACHE_TAG));
        }

        return $collectionToEncode;
    }

    protected function _resizeImage(Mage_Catalog_Model_Product $product, $imgType, $itemType)
    {
        $imgHelper  = Mage::helper('catalog/image');
        $resize     = ($imgType === 'thumbnail') ? 150 : 450;
        $braceletScale  = 0.17763845;
        $beadScale      = $braceletScale / 2;

        $imgPath    = $product->getData($imgType);
        $baseDir    = Mage::getBaseDir('media') . '/catalog/product';

        if ($imgPath && file_exists($baseDir . $imgPath)) {
            $filePath = $baseDir . $imgPath;

            /**
             * I know, I know...error suppression is usually bad, but the getimagesize()
             * will only produce E_WARNING (if accessing file is impossible) or
             * E_NOTICE for a read error
             *
             * @see  http://php.net/manual/en/function.getimagesize.php
             */
            list($imgWidth, $imgHeight) = getimagesize($filePath);

            // Mage::log('$imgWidth : ' . var_export($imgWidth, true));
            // Mage::log('$imgHeight : ' . var_export($imgHeight, true));

            switch (true) {
                case ($imgType === 'thumbnail'):
                    $resizeWidth    = 150;
                    $resizeHeight   = 150;
                    break;

                case ($imgType === 'designer_canvas'):
                    if ($itemType === 'bracelet') {
                        $resizeWidth    = 340;
                        $resizeHeight   = 358;
                    } else {
                        $resizeWidth    = round($imgWidth * $beadScale);
                        $resizeHeight   = round($imgHeight * $beadScale);
                    }
                    break;
                default:
                    $resizeWidth    = 340;
                    $resizeHeight   = 358;
                    break;
            }

            $imgUrl = (string) $imgHelper->init($product, $imgType)
                ->constrainOnly(TRUE)
                ->keepAspectRatio(TRUE)
                ->keepFrame(FALSE)
                ->resize($resizeWidth, $resizeHeight);
        } else {
            $imgUrl = null;
        }

        return $imgUrl;
    }


    /**
     * Return the selected option's front-end label or default to the optionId
     * if the attribute isn't a select or multiselect input
     *
     * @param  string   $attrCode   # i.e., 'color'
     * @param  integer  $optionId   # selected option id value
     * @return string
     */
    public function getAttrOptionLabel($attrCode, $optionId)
    {
        // default return value
        $label  = (empty($optionId)) ? null : $optionId;
        $attr   = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attrCode);

        if ($attr && $attr->usesSource() && !is_null($optionId)) {
            $label = $attr->getSource()->getOptionText($optionId);
        }

        return $label;
    }

    /**
     * Getter method for $_visibilityOptions
     *
     * @return array
     */
    public function getVisibilityOptions()
    {
        return Mage_Catalog_Model_Product_Visibility::getOptionArray();
    }

    /**
     * Getter method for $_attrsToSelect
     *
     * @return mixed|array
     */
    public function getAttrsToSelect()
    {
        return $this->_attrsToSelect;
    }

    /**
     * Setter method for $_attrsToSelect
     *
     * Attrs can be an array('sku', 'name', ...) or in string format (e.g., '*')
     *
     * @param mixed|array|string    $attrs
     */
    public function setAttrsToSelect($attrs)
    {
        $this->_attrsToSelect = $attrs;
        return $this;
    }


    /**
     * Getter method for $_attrsToSkip
     *
     * @return array
     */
    public function getAttrsToSkipInJson()
    {
        return $this->_attrsToSkip;
    }

    /**
     * Setter method for $_attrsToSkip
     *
     * Attrs can be an array('attribute_set_id', 'cat_index_position', ...)
     *
     * @param array    $attrs
     */
    public function setAttrsToSkipInJson(array $attrs)
    {
        $this->_attrsToSkip = $attrs;
        return $this;
    }

    /**
     * Getter method for $_attrsToFetchLabelsFrom
     *
     * @return array
     */
    public function getAttrsToFetchLabelsFrom()
    {
        return $this->_attrsToFetchLabelsFrom;
    }

    /**
     * Setter method for $_attrsToFetchLabelsFrom
     *
     * Attrs can be an array('size', 'color', 'material', ...)
     *
     * @param array    $attrs
     */
    public function setAttrsToFetchLabelsFrom(array $attrs)
    {
        $this->_attrsToFetchLabelsFrom = $attrs;
        return $this;
    }


    /**
     * Getter method for $_excludedCategoryIds
     *
     * @return array
     */
    public function getExcludedCategoryIds()
    {
        return $this->_excludedCategoryIds;
    }

    /**
     * Setter method for $_excludedCategoryIds
     *
     * $categoryIds can be an array(594, 689, ...)
     *
     * @param array     c$ategoryIds
     */
    public function setExcludedCategoryIds(array $categoryIds = array())
    {
        $this->_excludedCategoryIds = $categoryIds;
        return $this;
    }

    public function getCache($cacheKey)
    {
        return $this->_getCache($cacheKey);
    }

    protected function _getCache($cacheKey)
    {
        $cache = $this->_getCacheStorage();
        return $cache->load($cacheKey);
    }

    protected function _getCacheStorage()
    {
        if (!isset($this->_cacheStorage)) {
            $this->_cacheStorage = Mage::app()->getCache();
        }

        return $this->_cacheStorage;
    }
}
