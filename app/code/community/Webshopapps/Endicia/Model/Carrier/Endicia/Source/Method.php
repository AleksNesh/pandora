<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Endicia
 * User         Genevieve Eddison
 * Date         13 November 2013
 * Time         11:00 AM
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */
class Webshopapps_Endicia_Model_Carrier_Endicia_Source_Method
{
    public function toOptionArray()
    {
        $endicia = Mage::getSingleton('wsaendicia/carrier_endicia');

        $arr = array();
        foreach ($endicia->getCode('method') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }

    public function getDomesticMethods()
    {
        $endicia = Mage::getSingleton('wsaendicia/carrier_endicia');
        return $endicia->getCode('method_domestic');
    }

    public function getInternationalMethods()
    {
        $endicia = Mage::getSingleton('wsaendicia/carrier_endicia');
        return $endicia->getCode('method_international');
    }
}