<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_PrintType
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('Image and PDF'), 'value' => 'GIF'),
            array('label' => Mage::helper('upslabel')->__('EPL2'), 'value' => 'EPL'),
            array('label' => Mage::helper('upslabel')->__('SPL'), 'value' => 'SPL'),
            array('label' => Mage::helper('upslabel')->__('ZPL'), 'value' => 'ZPL'),
            array('label' => Mage::helper('upslabel')->__('STAR'), 'value' => 'STARPL'),
        );
        return $c;
    }
}