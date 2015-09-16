<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Exampleextension
 * User         karen
 * Date         03/07/2013
 * Time         00:30
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

class Webshopapps_Wsacommon_Model_Usa_Shipping_Carrier_Ups extends Mage_Usa_Model_Shipping_Carrier_Ups {


    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // Dimensional Shipping takes precendence over UpsCalendar
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
            if (!Mage::registry('shipusa_upsmodel')) {
                $model = Mage::getModel('shipusa/shipping_carrier_ups');
                Mage::register('shipusa_upsmodel', $model);
            }
            return Mage::registry('shipusa_upsmodel')->collectRates($request);
        }

        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Upscalendar','shipping/webshopapps_dateshiphelper/active')) {
            if (!Mage::registry('upscalendar_upsmodel')) {
                $model = Mage::getModel('upscalendar/usa_shipping_carrier_ups');
                Mage::register('upscalendar_upsmodel', $model);
            }
            return Mage::registry('upscalendar_upsmodel')->collectRates($request);
        }

        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_UPSDateShipping','shipping/webshopapps_dateshiphelper/active')) {
            if (!Mage::registry('upsdateshipping_upsmodel')) {
                $model = Mage::getModel('webshopapps_upsdateshipping/usa_shipping_carrier_ups');
                Mage::register('upsdateshipping_upsmodel', $model);
            }
            return Mage::registry('upsdateshipping_upsmodel')->collectRates($request);
        }

        // default
        return parent::collectRates($request);
    }

    /**
     * UC-17
     * Added to ensure the max weight doesn't come into play with time in transit when used with dim shipping.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Shipping_Model_Carrier_Abstract|Mage_Shipping_Model_Rate_Result_Error
     */
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
            return  Mage::getModel('shipusa/shipping_carrier_ups')->proccessAdditionalValidation($request);
        } else {
            return parent::proccessAdditionalValidation($request);
        }
    }
}