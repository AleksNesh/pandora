<?php 
class Magestore_Giftwrap_Block_Adminhtml_Product_Renderer_Wrappable
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if((int)$row->getGiftwrap()== Magestore_Giftwrap_Model_Giftwrap::STATUS_ENABLED)
			return $this->__('Yes');
		if((int)$row->getGiftwrap()== Magestore_Giftwrap_Model_Giftwrap::STATUS_DISABLED)
			return $this->__('No');	
	}
}