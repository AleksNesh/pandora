<?php
class EM_Megamenupro_Block_Adminhtml_Megamenupro extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_megamenupro';
    $this->_blockGroup = 'megamenupro';
    $this->_headerText = Mage::helper('megamenupro')->__('EMThemes Menu Manager');
    $this->_addButtonLabel = Mage::helper('megamenupro')->__('Add New Menu');
	
	 $this->_addButton("flush", array( 
		'label' => Mage::helper('megamenupro')->__('Flush cache Megamenupro'),
		'onclick'	=>	 "setLocation('".$this->getUrl('*/*/flushcache')."')",
		'class'		=>	 'delete',
		), -100);
	
    parent::__construct();
  }
}