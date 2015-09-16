<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Table
*/
class Amasty_Table_Model_Config_Source_Ziptype extends Varien_Object
{
    public function toOptionArray()
    {
        $vals = array(
            '0' => Mage::helper('amtable')->__('Numeric'),
            '1'   => Mage::helper('amtable')->__('String'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                    'value' => $k,
                    'label' => $v
            );
        
        return $options;
    }
}