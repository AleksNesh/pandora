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
class Webshopapps_Endicia_Model_Carrier_Endicia_Source_Recreditamount
{
    public function toOptionArray()
    {
        $endicia = Mage::getSingleton('wsaendicia/carrier_endicia');

        $arr = array();
        foreach ($endicia->getCode('recredit') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        array_unshift($arr, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        return $arr;
    }
}