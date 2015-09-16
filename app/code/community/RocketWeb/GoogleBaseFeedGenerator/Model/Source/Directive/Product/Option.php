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

class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Product_Option extends Varien_Object
{

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        $code = Mage::app()->getRequest()->getParam('store', false);
        $store_id = $code ? Mage::getModel('core/store')->load($code)->getId() : 0;
        $options = Mage::getModel('catalog/product_option')->getCollection()->addTitleToResult($store_id);
        $options->getSelect()->columns('COUNT(*) AS count_products')->group('title');

        $data = array(
            array('value' => '', 'label' => Mage::helper('googlebasefeedgenerator')->__('-- select one --')),
        );

        foreach ($options as $option) {
            $data[] = array('value' => $option->getTitle(), 'label' => $option->getTitle(). ' ('. $option->getCountProducts(). ')');
        }

        return $data;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = '<select name="groups[#{group_name}][fields][#{field_name}][value][#{_id}][param][]" class="select multiselect" multiple="multiple" size="5">';

        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $html .= '</select>';

        $html .= '<p class="note" style="clear:both;"><span>' . Mage::helper('googlebasefeedgenerator')->__('Numbers on the right represent the products count by each option.') . '</span></p>';
        return $html;
    }
}