<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_TermalWidth
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => Mage::helper('upslabel')->__('6'), 'value' => '6'),
            array('label' => Mage::helper('upslabel')->__('8'), 'value' => '8'),
        );
        return $c;
    }
}