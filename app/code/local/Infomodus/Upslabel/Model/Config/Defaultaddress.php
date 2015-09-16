<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Defaultaddress
{
    public function toOptionArray()
    {
        

        $c = array();
        for($i=1; $i<=10; $i++){
            if(Mage::getStoreConfig('upslabel/address_'.$i.'/enable')==1){
                $c[] = array('label' => Mage::helper('adminhtml')->__(Mage::getStoreConfig('upslabel/address_'.$i.'/addressname')), 'value' => $i);
            }
        }
        return $c;
    }
}