<?php
class Magestore_Giftwrap_Model_System_Config_Source_Styles
{
    public function toOptionArray ()
    {
        return array(
        array('value' => 'pinky', 'label' => Mage::helper('adminhtml')->__('Pinky')), 
        array('value' => 'default', 'label' => Mage::helper('adminhtml')->__('Default')));
    }
}