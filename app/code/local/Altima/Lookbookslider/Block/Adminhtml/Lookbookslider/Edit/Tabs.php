<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Block_Adminhtml_Lookbookslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('lookbookslider_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('lookbookslider')->__('Slider Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('lookbookslider')->__('General Information'),
          'title'     => Mage::helper('lookbookslider')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit_tab_form')->toHtml(),
        ))->addTab('widget_section', array(
          'label'     => Mage::helper('lookbookslider')->__('Widget Information'),
          'title'     => Mage::helper('lookbookslider')->__('Widget Information'),
          'content'   => $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit_tab_widget')->toHtml(),
        ))->addTab('page_section', array(
            'label'     => Mage::helper('lookbookslider')->__('Display on CMS Pages'),
            'title'     => Mage::helper('lookbookslider')->__('Display on CMS Pages'),
            'content'   => $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit_tab_page')->toHtml(),
        ))->addTab('category_section', array(
            'label'     => Mage::helper('lookbookslider')->__('Display on Categories'),
            'title'     => Mage::helper('lookbookslider')->__('Display on Categories'),
            'content'   => $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit_tab_category')->toHtml(),
        ));
      return parent::_beforeToHtml();
  }
}