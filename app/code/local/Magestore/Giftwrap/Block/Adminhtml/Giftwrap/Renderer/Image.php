<?php 
class Magestore_Giftwrap_Block_Adminhtml_Giftwrap_Renderer_Image
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		$path = Mage::getBaseUrl('media').'giftwrap/'.$row->getImage();
		return '<img src="'.$path.'" width="80px">';
	}
}