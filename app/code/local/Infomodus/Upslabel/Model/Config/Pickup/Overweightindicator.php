<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Overweightindicator
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'Choose', 'value' => ""),
            array('label' => Mage::helper('adminhtml')->__('Over weight'), 'value' => 'Y'),
            array('label' => Mage::helper('adminhtml')->__('Not over weight'), 'value' => 'N'),
        );
        return $c;
    }
}