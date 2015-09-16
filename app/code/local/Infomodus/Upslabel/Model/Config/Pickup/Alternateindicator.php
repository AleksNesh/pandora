<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Alternateindicator
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('Choose'), 'value' => ""),
            array('label' => Mage::helper('upslabel')->__('Alternate address'), 'value' => 'Y'),
            array('label' => Mage::helper('upslabel')->__('Original pickup address'), 'value' => 'N'),
        );
        return $c;
    }
}