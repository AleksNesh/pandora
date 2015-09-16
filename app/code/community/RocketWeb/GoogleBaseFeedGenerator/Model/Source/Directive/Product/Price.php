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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Product_Price extends Varien_Object
{

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('googlebasefeedgenerator')->__('Yes')),
            array('value' => 0, 'label' => Mage::helper('googlebasefeedgenerator')->__('No')),
        );
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = '<div style="float:left;">'. Mage::helper('googlebasefeedgenerator')->__('Add Tax:'). '</div>'
            . '<div style="float:right;"><select name="groups[#{group_name}][fields][#{field_name}][value][#{_id}][param]" class="select" style="width:180px;">';

        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $html .= '</select></div>';

        $html .= '<p class="note" style="clear:both;"><span>' . Mage::helper('googlebasefeedgenerator')->__('US feeds should not include tax.') . '</span></p>';
        return $html;
    }
}