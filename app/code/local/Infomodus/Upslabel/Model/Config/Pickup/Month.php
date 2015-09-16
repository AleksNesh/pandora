<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Month
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('Choose'), 'value' => ""),
            array('label' => Mage::helper('upslabel')->__('Current'), 'value' => 0),
        );
        for($i=1; $i<13; $i++){
            $c[] = array('label' => $i, 'value' => ($i<10?'0'.$i:$i));
        }
        return $c;
    }
}