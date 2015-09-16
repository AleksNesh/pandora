<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Year
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('Choose'), 'value' => ""),
            array('label' => Mage::helper('upslabel')->__('Current'), 'value' => 0),
            array('label' => date("Y"), 'value' => date("Y")),
            array('label' => date("Y")+1, 'value' => date("Y")+1),
        );
        return $c;
    }
}