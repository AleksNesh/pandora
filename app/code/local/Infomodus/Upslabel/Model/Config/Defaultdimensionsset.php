<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Defaultdimensionsset
{
    public function toOptionArray()
    {
        

        $c = array();
        $c[] = array('label' => Mage::helper('adminhtml')->__('Custom dimensions'), 'value' => 0);
        for($i=1; $i<=15; $i++){
        if(Mage::getStoreConfig('upslabel/dimansion_'.$i.'/enable')==1){
            $c[] = array('label' => Mage::helper('adminhtml')->__(Mage::getStoreConfig('upslabel/dimansion_'.$i.'/dimansionname')), 'value' => $i);
        }
        }
        return $c;
    }
}