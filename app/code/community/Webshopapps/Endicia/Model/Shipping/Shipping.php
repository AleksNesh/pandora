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

class Webshopapps_Endicia_Model_Shipping_Shipping extends Webshopapps_Wsacommon_Model_Shipping_Shipping
{

    /**
     * Prepare and do request to shipment
     *
     * @param Mage_Sales_Model_Order_Shipment $orderShipment
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Sales_Model_Order_Shipment $orderShipment)
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $order = $orderShipment->getOrder();
        $address = $order->getShippingAddress();
        $selectedShipMethod = $this->_mapShipMethod($order->getShippingMethod(true), $order->getShippingCarrier(), $address);
        $shipmentStoreId = $orderShipment->getStoreId();
        $shipmentCarrier = Mage::getModel('wsaendicia/carrier_endicia');


        $shippingMethod = $this->_getShipmentCarrier($selectedShipMethod, $shipmentCarrier->getCarrierCode());

        $baseCurrencyCode = Mage::app()->getStore($shipmentStoreId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            Mage::throwException('Invalid carrier: ' . $shippingMethod->getCarrierCode());
        }
        $shipperRegionCode = Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $shipmentStoreId);
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = Mage::getModel('directory/region')->load($shipperRegionCode)->getCode();
        }

        $recipientRegionCode = Mage::getModel('directory/region')->load($address->getRegionId())->getCode();

        $originStreet1 = Mage::getStoreConfig(self::XML_PATH_STORE_ADDRESS1, $shipmentStoreId);
        $originStreet2 = Mage::getStoreConfig(self::XML_PATH_STORE_ADDRESS2, $shipmentStoreId);
        $storeInfo = new Varien_Object(Mage::getStoreConfig('general/store_information', $shipmentStoreId));

        if (!$admin->getFirstname() || !$admin->getLastname() || !$storeInfo->getName() || !$storeInfo->getPhone()
            || !$originStreet1 || !Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $shipmentStoreId)
            || !$shipperRegionCode || !Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $shipmentStoreId)
            || !Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId)
        ) {
            Mage::throwException(
                Mage::helper('sales')->__('Insufficient information to create shipping label(s). Please verify your Store Information and Shipping Settings.')
            );
        }

        /** @var $request Mage_Shipping_Model_Shipment_Request */
        $request = Mage::getModel('shipping/shipment_request');
        $request->setOrderShipment($orderShipment);
        $request->setShipperContactPersonName($admin->getName());
        $request->setShipperContactPersonFirstName($admin->getFirstname());
        $request->setShipperContactPersonLastName($admin->getLastname());
        $request->setShipperContactCompanyName($storeInfo->getName());
        $request->setShipperContactPhoneNumber($storeInfo->getPhone());
        $request->setShipperEmail($admin->getEmail());
        $request->setShipperAddressStreet(trim($originStreet1 . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet1);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $shipmentStoreId));
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode(Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $shipmentStoreId));
        $request->setShipperAddressCountryCode(Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId));
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreet1() . ' ' . $address->getStreet2()));
        $request->setRecipientAddressStreet1($address->getStreet1());
        $request->setRecipientAddressStreet2($address->getStreet2());
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode());
        $request->setRecipientAddressRegionCode($recipientRegionCode);
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);

        return $shipmentCarrier->requestToShipment($request);
    }

    protected function _getShipmentCarrier($method, $carrierCode)
    {
        return new Varien_Object(array(
            'carrier_code' => $carrierCode,
            'method'       => $method
        ));
    }

    protected function _mapShipMethod($shipMethod, $shipCarrier, $address)
    {
        if(!Mage::helper('wsaendicia')->isDomestic($address->getCountryId()))
        {
            return Mage::getStoreConfig('carriers/wsaendicia/default_international');
        }
        return Mage::getStoreConfig('carriers/wsaendicia/default_domestic');
    }
}