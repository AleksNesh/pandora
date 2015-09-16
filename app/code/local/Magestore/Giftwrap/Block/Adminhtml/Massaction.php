
<?php
//Giftwarp filter King_211112
class Magestore_Giftwrap_Block_Adminhtml_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction
{
	 public function __construct()
    {	
        parent::__construct(); 
        $this->setTemplate('giftwrap/massaction.phtml');
        $this->setErrorText(Mage::helper('catalog')->jsQuoteEscape(Mage::helper('catalog')->__('Please select items.')));
    }
}