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
 * @copyright Copyright (c) 2015 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_GoogleBaseFeedGenerator_Block_Product_Configurable_Remarketing extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $product = Mage::registry('current_product');
        if ($product->isConfigurable()) {
            $this->setData('products', Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product));
        }
        parent::_construct();
    }
}