<?php
/**
* Ash Slideshow Extension
*
* @category  Ash
* @package   Ash_Slideshow
* @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* @author    August Ash Team <core@augustash.com>
*
**/

class Ash_Slideshow_Block_Adminhtml_Slideshow_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    /**
     * Magento's class contructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('ash_slideshow');
        $this->setId('slideshow_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_helper->__('Slideshow'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section_info', array(
            'label'     => $this->_helper->__('Information'),
            'title'     => $this->_helper->__('Information'),
            'content'   => $this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_edit_tab_form')->toHtml(),
        ));

        $this->addTab('form_section_settings', array(
            'label'     => $this->_helper->__('Settings'),
            'title'     => $this->_helper->__('Settings'),
            'content'   => $this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_edit_tab_settings')->toHtml(),
        ));

        $this->addTab('form_section_assets', array(
            'label'     => $this->_helper->__('Assets'),
            'title'     => $this->_helper->__('Assets'),
            'content'   => $this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_edit_tab_assets')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
