<?php
/**
 * @copyright   Copyright (c) 2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Sorting_Model_Source_Methods
{
    public function toOptionArray()
    {
        $options = array();
        
        // magento wants at least one option to be selected
        $options[] = array(
            'value' => 'none',
            'label' => '',
            
        );         
        foreach (Mage::helper('amsorting')->getMethods() as $className){
            $method = Mage::getSingleton('amsorting/method_' . $className);  
            $options[] = array(
                'value' => $method->getCode(),
                'label' => Mage::helper('amsorting')->__($method->getName()),
                
            );
        }   
        return $options;
    }
}