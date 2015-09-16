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
class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract_Associated extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
{

    /**
     * Do not run options for associated products as options are associated products
     *
     * @return $this
     */
    public function _beforeMap()
    {
        return $this;
    }

    /**
     * @param array $params
     * @return mixed|string
     */
    public function mapDirectiveUrl($params = array())
    {
        $args = array('map' => $params['map']);
        $product = $this->getProduct();

        if (!$this->hasParentMap()) {
            return parent::mapDirectiveUrl($params);
        }

        switch ($this->getConfigVar('associated_products_link', 'configurable_products')) {
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated_Link::FROM_PARENT:
                $value = $this->hasParentMap() ? $this->getParentMap()->mapColumn($args['map']['column']) : '';
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated_Link::FROM_ASSOCIATED_PARENT:
                if ($product->isVisibleInSiteVisibility()) {
                    return parent::mapDirectiveUrl($params);
                } else {
                    $value = $this->hasParentMap() ? $this->getParentMap()->mapColumn($args['map']['column']) : parent::mapDirectiveUrl($params);
                }
                break;

            default:
                $value = $this->hasParentMap() ? $this->getParentMap()->mapColumn($args['map']['column']) : '';
        }

        // Add unique URLs to associated of bundle and configurable if the config is set.
        if ($this->hasParentMap()) {

            $typeId = $this->getParentMap()->getProduct()->getTypeId();
            $linkAddUnique = $this->getConfigVar('associated_products_link_add_unique', 'configurable_products')
                && ($typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE || $typeId == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);

            if ($linkAddUnique) {
                $value = $this->addUrlUniqueParams($value, $this->getProduct(), $this->getParentMap()->getOptionCodes($this->getProduct()->getEntityId()), $typeId);
            }
        }

        return $value;
    }


    public function getPrice($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (Mage::helper('googlebasefeedgenerator')->isModuleEnabled('Aitoc_Aitcbp')) {
            $product = $product->load($product->getid());
        }

        $price = ($pMap = $this->getParentMap()) ? $pMap->getCacheAssociatedPrice($product->getId()) : false;

        if (!$price) {
            $price = $product->getPrice();
        }
        return $price;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnAvailability($params = array())
    {
        $args = array('map' => $params['map']);

        if ($this->hasParentMap()) {
            $value = $this->getParentMap()->mapColumn('availability');
            // Gets out of stock if parent is out of stock
            if ($this->getConfigVar('inherit_parent_out_of_stock', 'configurable_products') && strcasecmp($this->getConfig()->getOutOfStockStatus(), $value) == 0) {
                return $value;
            }
        }

        return $this->getCellValue($args);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnBrand($params = array())
    {
        $args = array('map' => $params['map']);
        $value = $this->getCellValue($args);

        if (empty($value)) {
            $value = $this->hasParentMap() ? $this->getParentMap()->mapColumn('brand') : '';
        }
        $this->_findAndReplace($value, $params['map']['column']);

        return $value;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveGoogleCategoryByCategory($params = array())
    {
        // try to get value from parent first
        $value = $this->hasParentMap() ? $this->getParentMap()->mapDirectiveGoogleCategoryByCategory($params) : '';
        if (empty($value)) {
            $value = parent::mapDirectiveGoogleCategoryByCategory($params);
        }
        return $value;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveProductTypeByCategory($params = array())
    {
        // try to get value from parent first
        $value = $this->hasParentMap() ? $this->getParentMap()->mapDirectiveProductTypeByCategory($params): '';
        if (empty($value)) {
            $value = parent::mapDirectiveProductTypeByCategory($params);
        }
        return $value;
    }

    /**
     * @param $params
     * @param $attributes_codes
     * @return string
     */
    public function mapDirectiveVariantAttributes($params = array())
    {
        // try to get value from parent first
        $value = $this->hasParentMap() ? $this->getParentMap()->mapDirectiveVariantAttributes($params): '';
        if (empty($value)) {
            $value = parent::mapDirectiveVariantAttributes($params);
        }
        return $value;
    }
}
