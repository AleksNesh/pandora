<?php
class Magestore_Giftwrap_Model_System_Config_Source_Product_View_Type
{
    public function toOptionArray ()
    {
        return array(
        array('value' => 'radio', 'label' => Mage::helper('adminhtml')->__('Radio')), 
        array('value' => 'dropdown', 'label' => Mage::helper('adminhtml')->__('Dropdown')));
    }
}