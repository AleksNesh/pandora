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

class RocketWeb_GoogleBaseFeedGenerator_Block_Adminhtml_System_Config_Form_Field_Categorytree
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Generate HtmlId across the entire tree widget
     */
    protected function _construct()
    {
        $this->setHtmlId('_' . uniqid());
    }

    /**
     * Set up the widget
     * @return $this|Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('googlebasefeedgenerator/system/config/form/field/categorytree.phtml');

        $tree_block = $this->getLayout()
            ->createBlock('googlebasefeedgenerator/adminhtml_catalog_category_checkboxes_tree')
            ->setHtmlId($this->getHtmlId())
            ->setJsFormObject($this->getJsFormObject());

        $this->setChild('feed_categories_include_tree', $tree_block);
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        return $this;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $ids = explode(',', $element->getValue());
        if (count($ids) == 1 && empty($ids[0])) {
            $ids = null;
        }
        $this->getChild('feed_categories_include_tree')->setCategoryIds($ids);
        return $this->_toHtml();
    }

    public function getJsFormObject()
    {
        return 'categories_include_form';
    }

    public function getLabel() {
        return $this->__('Include all categories');
    }

    public function getNote() {
        return '<p class="note">'. $this->__('To include children categories:'). '
                <br>&#8226;'. $this->__('either use <a target="_blank" href="http://www.magentocommerce.com/knowledge-base/entry/anchor-categories">Anchor categories</a> to auto-include products from sub-categories'). '
                <br>&#8226;'. $this->__('or select all sub-categories you wish to include in the feed').' </li>
                </p>
                <p class="note">'. $this->__('Please have your <b>Category Product Index</b> in the green for anchors to work as expected'). '</p>';
    }
}