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
class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Grouped_Associated extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
{

    /**
     * @param array $params
     * @return mixed|string
     */
    public function mapColumnLink($params = array())
    {
        $args = array('map' => $params['map']);
        $product = $this->getProduct();
        $add_unique = $this->getConfigVar('associated_products_link_add_unique', 'grouped_products');

        switch ($this->getConfigVar('associated_products_link', 'grouped_products')) {
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Groupedassocprodslink::FROM_PARENT:
                $value = $this->getParentMap()->mapColumn('link');
                if ($add_unique) {
                    $value = $this->addUrlUniqueParams($value, $product, null);
                }
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Groupedassocprodslink::FROM_ASSOCIATED_PARENT:
                if ($product->isVisibleInSiteVisibility()) {
                    $value = $this->getCellValue($args);
                } else {
                    $value = $this->getParentMap()->mapColumn('link');
                    if ($add_unique) {
                        $value = $this->addUrlUniqueParams($value, $product, null);
                    }
                }
                break;

            default:
                $value = $this->getParentMap()->mapColumn('link');
                if ($add_unique) {
                    $value = $this->addUrlUniqueParams($value, $product, null);
                }
        }

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
        $value = $this->hasParentMap() ? $this->getParentMap()->mapDirectiveProductTypeByCategory($params) : '';
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
        $value = $this->hasParentMap() ? $this->getParentMap()->mapDirectiveVariantAttributes($params) : '';
        if (empty($value)) {
            $value = parent::mapDirectiveVariantAttributes($params);
        }
        return $value;
    }
}