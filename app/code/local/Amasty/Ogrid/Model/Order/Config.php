<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Model_Order_Config
{
    function toOptionArray() {
        $ret = array();
        $statuses =  Mage::getSingleton('sales/order_config')->getStatuses();
        foreach($statuses as $value => $label)
            $ret[] = array(
                'value' => $value,
                'label' => $label
            );
        return $ret;
    }
}
?>