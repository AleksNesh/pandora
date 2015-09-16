<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Shipdesc
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('Customer name + Order Id'), 'value' => '1'),
            array('label' => Mage::helper('upslabel')->__('Only Customer name'), 'value' => '2'),
            array('label' => Mage::helper('upslabel')->__('Only Order Id'), 'value' => '3'),
            array('label' => Mage::helper('upslabel')->__('nothing'), 'value' => ''),
        );
        return $c;
    }
}