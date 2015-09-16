<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_DeliveryConfirmation
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('adminhtml')->__('Not used'), 'value' => 0),
            array('label' => Mage::helper('adminhtml')->__('Delivery Confirmation'), 'value' => 1),
            array('label' => Mage::helper('adminhtml')->__('Delivery Confirmation Signature Required'), 'value' => 2),
            array('label' => Mage::helper('adminhtml')->__('Delivery Confirmation Adult Signature Required'), 'value' => 3),
            array('label' => Mage::helper('adminhtml')->__('USPS Delivery Confirmation'), 'value' => 4),
        );
        return $c;
    }
}