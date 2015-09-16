<?php

class TinyBrick_Authorizenetcim_Model_Adminhtml_Config_Enabled extends Mage_Core_Model_Config_Data
{
    
	public function toOptionArray()
    {
    	$result = array();
        $result[] = array(
        	'label' => "Disabled",
            'value' => "0"
            );
        if(Mage::helper('authorizenetcim')->isEnabled()){
	        $result[] = array(
	            'label' => "Enabled",
	            'value' => "1"
	            );
        }
        return $result;
    }
    
}
