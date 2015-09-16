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
class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Bundle extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
{
    protected $_assoc_bundle_option = array();

    /**
     * Support for configurable items product option not yet implemented
     * @return bool
     */
    protected function _isAllowProductOptions()
    {
        return false;
    }

    public function getChildrenCount() {
        $options = $this->getProduct()->getTypeInstance(true)->getOptionsIds($this->getProduct());
        return (count($options));
    }

    /**
     * Iterate through associated products and set mapping objects
     *
     * @return $this
     */
    public function _beforeMap()
    {
        if ($this->isSkip()) {
            return $this;
        }

        $this->_assocs = array();
        $bundleType = $this->getProduct()->getTypeInstance(true);

        $optionIds = $bundleType->getOptionsIds($this->getProduct());
        if ($optionIds) {
            $assocCollection = $bundleType->getSelectionsCollection($optionIds, $this->getProduct());
        }

        $stockStatusFlag = false;
        $stockStatus = false;

        foreach ($assocCollection as $option) {

            $assocId = $option->product_id;

            $assoc = Mage::getModel('catalog/product');
            $assoc->setStoreId($this->getStoreId());
            $assoc->getResource()->load($assoc, $assocId);

            if ($this->getGenerator()->getData('verbose')) {
                echo $this->getGenerator()->formatMemory(memory_get_usage(true)) . " - Bundle associated SKU " . $assoc->getSku() . ", ID " . $assoc->getId() . "\n";
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
            if ($this->getConfigVar('add_out_of_stock', 'filters')) {
                $this->_assocs[$assocId] = $assoc;
                $this->_assoc_ids[] = $assocId;
            } elseif ($stock == $this->getConfig()->getInStockStatus()) {
                $this->_assocs[$assocId] = $assoc;
                $this->_assoc_ids[] = $assocId;
            } else {
                // Set skip messages
                if ($this->getConfigVar('log_skip')) {
                    $this->log(sprintf("product id %d sku %s, configurable item, skipped - out of stock", $assocId, $assoc->getSku()));
                }
            }

            // Set stock status of the current item and check if the status has changed
            if ($stockStatus != false && $stock != $stockStatus) {
                $stockStatusFlag = true;
            }
            $stockStatus = $stock;
        }

        // Set bundle stock status if all assocs have the same stock status, only for default stocks
        if ($this->getConfigVar('use_default_stock', 'columns') && $stockStatus && !$stockStatusFlag) {
            $this->setAssociatedStockStatus($stockStatus);
            if ($stockStatus == $this->getConfig()->getOutOfStockStatus() && !$this->getConfigVar('add_out_of_stock', 'filters')) {
                $this->setSkip(sprintf("product id %d sku %s, bundle, skipped - out of stock.", $this->getProduct()->getId(), $this->getProduct()->getSku()));
            }
        }

        return parent::_beforeMap();
    }

    /**
     * @param null $product
     * @return float|mixed
     */
    public function getPrice($product = null, $includingTax = false)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (Mage::helper('googlebasefeedgenerator')->isModuleEnabled('Aitoc_Aitcbp')) {
            $product = $product->load($product->getid());
        }

        //remove special price from calculation - this is the only way to get the price before discount
        $specialPrice = $product->getSpecialPrice();
        if (!empty($specialPrice)) {
            $product->setSpecialPrice('0');

            //force re-calculation
            $product->setData('min_price', '');
            $product->setData('max_price', '');
            $product->setFinalPrice(null);
        }

        $price = $this->calcMinimalPrice($product, $includingTax);

        //put special price back
        $product->setSpecialPrice($specialPrice);

        if ($price <= 0) {
            $price = $product->getPrice();
        }

        return $price;
    }

    /**
     * @param $product
     * @return float|mixed
     */
    public function calcMinimalPrice($product, $includingTax = false)
    {
        if ($this->getConfig()->compareMagentoVersion(array('major' => 1, 'minor' => 6, 'revision' => 0, 'patch' => 0))
            && $this->getConfig()->compareMagentoVersion(array('major' => 1, 'minor' => 10, 'revision' => 1, 'patch' => 1), '!=')
        ) {
            $_prices = $product->getPriceModel()->getTotalPrices($product, 'min', $includingTax);
        } else {
            $_prices = $product->getPriceModel()->getPricesDependingOnTax($product, 'min', $includingTax);
        }
        if (is_array($_prices)) {
            $price = min($_prices);
        } else {
            $price = $_prices;
        }

        if ($includingTax) {
            // Fix rounding the price value - replicate frontend template logic
            $store = Mage::app()->getStore($this->getStoreId());
            $price = $store->convertPrice($price);
        }

        return $price;
    }

    /**
     * @param null $product
     * @return float|int
     */
    public function getSpecialPrice($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $price = $this->getPrice($product);
        $special_price_percent = $product->getSpecialPrice();
        if ($special_price_percent <= 0 || $special_price_percent > 100) {
            return 0;
        } else {
            $special_price = ($special_price_percent) * $price / 100;
            return $special_price;
        }
    }


    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveShippingWeight($params = array())
    {
        $map = $params['map'];
        $map['attribute'] = 'weight';
        $unit = $map['param'];

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getProduct();

        if ($product->getWeightType() == Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes_Extend::DYNAMIC
            || $this->getConfigVar('combined_weight', 'bundle_products'))
        {
            $weight = '';
            if (is_array($this->_assocs)) {
                $weight = 0;
                $bundleType = $this->getProduct()->getTypeInstance(true);
                $optionsCollection = $bundleType->getOptionsCollection($product);
                foreach ($optionsCollection as $option) {
                    if ($selections = $option->getSelections()) {
                        foreach ($selections as $selection) {
                            $minQty = $selection->getSelectionQty();
                            if ($minQty && array_key_exists($selection->getId(), $this->_assocs)) {
                                $weight += $minQty * $this->_assocs[$selection->getId()]->getWeight();
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $weight_attribute = $this->getGenerator()->getAttribute($map['attribute']);
            if ($weight_attribute === false) {
                Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
            }
            $weight = $this->getAttributeValue($product, $weight_attribute);
        }

        if ($weight != "") {
            if (strpos($weight, $unit) === false) {
                $weight = number_format((float)$weight, 2). ' '. $unit;
            }
        }

        return $this->cleanField($weight, $params);
    }

    /**
     * @return array
     */
    public function map()
    {
        $rows = array();
        $parentRow = null;
        $this->_beforeMap();

        if ($this->getConfig()->isAllowBundleMode($this->getStoreId())) {
            if (!$this->isSkip()) {

                // simulate parent::map() without clearing associated_maps from memory, as associated more could be on.
                $row = parent::_map();
                reset($row); $parentRow = current($row);

                // remove parent and skipping flag so that the associated items could still be processed.
                if ($this->isSkip()) {
                    $parentRow = null;
                }
            }
        }

        if ($this->getConfig()->isAllowBundleAssociatedMode($this->getStoreId()) && $this->hasAssocMaps()) {
            foreach ($this->getAssocMaps() as $assocMap) {

                $row = $assocMap->map();
                reset($row); $row = current($row);

                if (!$assocMap->isSkip()) {
                    $rows[] = $row;
                }
            }
        }

        // Fill in parent columns specified in $inherit_columns with values list from associated items
        if (!is_null($parentRow)) {
            $this->mergeVariantValuesToParent($parentRow, $rows);
            array_unshift($rows, $parentRow);
        }

        // if any of the associated not skipped, force add them to the feed
        if (count($rows)) {
            $this->unSkip();
        }

        return $this->_afterMap($rows);
    }

    /**
     * Returns a list of associated IDs with their corresponded option ID & Value on bundle page.
     *
     * @return array
     */
    private function _getAssocBundleOption()
    {
        if (empty($this->_assoc_bundle_option)) {

            $selectCollection = $this->getProduct()->getTypeInstance(true)->getSelectionsCollection(
                $this->getProduct()->getTypeInstance(true)->getOptionsIds($this->getProduct()), $this->getProduct()
            );
            foreach ($selectCollection as $item) {
                if (!array_key_exists($item->getEntityId(), $this->_assoc_bundle_option)) {
                    $this->_assoc_bundle_option[$item->getEntityId()] = array();
                }
                $this->_assoc_bundle_option[$item->getEntityId()][$item->getOptionId()] = $item->getSelectionId();
            }
        }
        return $this->_assoc_bundle_option;
    }

    /**
     * Returns list of option & value for a specific assoc.
     * TODO: if an associated is used in many options, this needs to return only the one option value which makes up current assoc product
     * see RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Associated::mapDirectiveUrl()
     *
     * @param null $assoc_id
     */
    public function getOptionCodes($assoc_id = null)
    {
        $assoc_bundle_option = $this->_getAssocBundleOption();

        if (is_null($assoc_id)) {
            return $assoc_bundle_option;
        } elseif (array_key_exists($assoc_id, $assoc_bundle_option)) {
            return $assoc_bundle_option[$assoc_id];
        }

        return array();
    }

    protected function mapDirectivePrice($params = array())
    {
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        $product = $this->getProduct();

        /**
         * @var $helper RocketWeb_GoogleBaseFeedGenerator_Helper_Tax
         */
        $helper = Mage::helper('googlebasefeedgenerator/tax');

        /* 0 - excluding tax
           1 - including tax */
        $priceIncludesTax = ($helper->priceIncludesTax($this->getStoreId()) ? true : false);
        $includingTax = (bool) $params['map']['param'];

        $this->_cache_price_excluding_tax = $this->getPrice($product, false);
        $this->_cache_price_including_tax = $this->getPrice($product, true);
        if ($includingTax) {
            $cell = $this->_cache_price_including_tax;
        } else {
            $cell = $this->_cache_price_excluding_tax;
        }

        unset($priceIncludesTax, $includingTax, $price, $helper);
        return $this->cleanField($cell, $params);
    }

    /**
     * Returns true for bundle items, and flase for the others.
     *
     * @param array $params
     * @return string
     */
    protected function mapDirectiveIsBundle($params = array())
    {
        return 'TRUE';
    }
}