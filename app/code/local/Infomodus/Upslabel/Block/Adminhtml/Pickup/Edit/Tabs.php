<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Pickup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('pickup_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('upslabel')->__('Pickup Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('upslabel')->__('Pickup Information'),
          'title'     => Mage::helper('upslabel')->__('Pickup Information'),
          'content'   => $this->getLayout()->createBlock('upslabel/adminhtml_pickup_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}