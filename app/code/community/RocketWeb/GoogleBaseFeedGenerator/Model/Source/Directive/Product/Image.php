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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Product_Image extends Varien_Object
{
    public function _construct()
    {
        $this->addData(array('param_label' => 'Type:', 'param_help' => 'Choose which product image should be considered.'));
    }

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'image', 'label' => Mage::helper('googlebasefeedgenerator')->__('Base Image')),
            array('value' => 'small_image', 'label' => Mage::helper('googlebasefeedgenerator')->__('Small Image')),
            array('value' => 'thumbnail', 'label' => Mage::helper('googlebasefeedgenerator')->__('Thumbnail')),
        );
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = '<div style="float:left;">'. Mage::helper('googlebasefeedgenerator')->__($this->getData('param_label')). '</div>'
            . '<div style="float:right;"><select name="groups[#{group_name}][fields][#{field_name}][value][#{_id}][param]" class="select" style="width:180px;">';

        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $html .= '</select></div>';

        $html .= '<p class="note" style="clear:both;"><span>' . Mage::helper('googlebasefeedgenerator')->__($this->getData('param_help')) . '</span></p>';
        return $html;
    }
}