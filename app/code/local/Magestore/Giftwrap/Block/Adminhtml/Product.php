<?php
class Magestore_Giftwrap_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
	$this->_controller = 'adminhtml_product';
    $this->_blockGroup = 'giftwrap';
    $this->_headerText = Mage::helper('giftwrap')->__('Manage Wrappable Products');

    parent::__construct();
	$this->_removeButton('add');
  }
}