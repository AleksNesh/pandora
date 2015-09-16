<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Servicecode
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('Choose'), 'value' => ""),
            array('label' => Mage::helper('upslabel')->__('UPS Next Day Air'), 'value' => '001'),
            array('label' => Mage::helper('upslabel')->__('UPS 2nd Day Air'), 'value' => '002'),
            array('label' => Mage::helper('upslabel')->__('UPS Ground'), 'value' => '003'),
            array('label' => Mage::helper('upslabel')->__('UPS Ground, UPS Standard'), 'value' => '004'),
            array('label' => Mage::helper('upslabel')->__('UPS Worldwide Express'), 'value' => '007'),
            array('label' => Mage::helper('upslabel')->__('UPS Worldwide Expedited'), 'value' => '008'),
            array('label' => Mage::helper('upslabel')->__('UPS Standard'), 'value' => '011'),
            array('label' => Mage::helper('upslabel')->__('UPS Three Day Select'), 'value' => '012'),
            array('label' => Mage::helper('upslabel')->__('UPS Next Day Air Saver'), 'value' => '013'),
            array('label' => Mage::helper('upslabel')->__('UPS Next Day Air Early A.M.'), 'value' => '014'),
            array('label' => Mage::helper('upslabel')->__('UPS Economy'), 'value' => '021'),
            array('label' => Mage::helper('upslabel')->__('UPS Basic'), 'value' => '031'),
            array('label' => Mage::helper('upslabel')->__('UPS Worldwide Express Plus'), 'value' => '054'),
            array('label' => Mage::helper('upslabel')->__('UPS Second Day Air A.M.'), 'value' => '059'),
            array('label' => Mage::helper('upslabel')->__('UPS Express NA1'), 'value' => '064'),
            array('label' => Mage::helper('upslabel')->__('UPS Saver'), 'value' => '065'),
            array('label' => Mage::helper('upslabel')->__('UPS Standard Today'), 'value' => '082'),
            array('label' => Mage::helper('upslabel')->__('UPS Today Dedicated Courier'), 'value' => '083'),
            array('label' => Mage::helper('upslabel')->__('UPS Intercity Today'), 'value' => '084'),
            array('label' => Mage::helper('upslabel')->__('UPS Today Express'), 'value' => '085'),
            array('label' => Mage::helper('upslabel')->__('UPS Today Express Saver'), 'value' => '086'),
            array('label' => Mage::helper('upslabel')->__('UPS Worldwide Express Freight'), 'value' => '096'),
        );
        return $c;
    }
}