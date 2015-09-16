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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Attributesets extends Varien_Object
{

    public function toOptionArray()
    {
        $entityTypeProduct = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $attributeSetOptions = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityTypeProduct)
            ->load()
            ->toOptionArray();

        $blank_option = array('value' => '', 'label' => 'All attribute sets');
        array_unshift($attributeSetOptions, $blank_option);
        return $attributeSetOptions;
    }
}

