<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Pickup_Stores
{
    public function toOptionArray()
    {
        $c = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    //print_r($store->getId()); exit;
                    $c[] = array('label' => $store->getName()." (".$website->getName()." \\ ".$group->getName().")", 'value' => $store->getId());
                }
            }
        }

        return $c;
    }

    public function getStores()
    {
        $c = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $c[$store->getId()] = $store->getName()." (".$website->getName()." \\ ".$group->getName().")";
                }
            }
        }
        return $c;
    }
}