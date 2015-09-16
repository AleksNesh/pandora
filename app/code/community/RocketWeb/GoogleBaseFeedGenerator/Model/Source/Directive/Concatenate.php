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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Concatenate extends Varien_Object
{

    public function toHtml()
    {
        $helper = Mage::helper('googlebasefeedgenerator');
        return '<textarea name="groups[#{group_name}][fields][#{field_name}][value][#{_id}][param]" class="textarea" style="height: auto" rows="2" cols="10">#{param}</textarea>
                <p class="note"><span>'. $helper->__(sprintf('Use product attributes in this format: {{attribute_code}}. Attribute codes can be found in %s', '<a href="'. Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product_attribute'). '" target="_blank">'. $helper->__('Manage Attributes').'</a>')). '. The following: {{name}}, {{description}}, {{url}} and {{image}}, will inherit values as defined in the \'Fetch ... from\' settings from Configurable and Grouped sections below.</span></p>';
    }
}