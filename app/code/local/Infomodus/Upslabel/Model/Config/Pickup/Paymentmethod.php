<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Paymentmethod
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'Choose', 'value' => ""),
            array('label' => Mage::helper('adminhtml')->__('No payment needed'), 'value' => '00'),
            array('label' => Mage::helper('adminhtml')->__('Pay by shipper account'), 'value' => '01'),
            array('label' => Mage::helper('adminhtml')->__('Pay by charge card'), 'value' => '03'),
            array('label' => Mage::helper('adminhtml')->__('Pay by tracking number'), 'value' => '04'),
            array('label' => Mage::helper('adminhtml')->__('Pay by check or money order'), 'value' => '05'),
        );
        return $c;
    }
}