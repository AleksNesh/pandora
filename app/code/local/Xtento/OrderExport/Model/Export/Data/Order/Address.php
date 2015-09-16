<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-03-30T18:54:19+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Order/Address.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Order_Address extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        // Init cache
        /*if (!isset($this->_cache['region_code'])) {
            $this->_cache['region_code'] = array();
        }*/
        // Return config
        return array(
            'name' => 'Billing/Shipping Address',
            'category' => 'Order',
            'description' => 'Export the billing/shipping address.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO, Xtento_OrderExport_Model_Export::ENTITY_QUOTE),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        // Billing Address
        $this->_writeArray = & $returnArray['billing']; // Write on billing level
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress && $billingAddress->getId()) {
            $billingAddress->explodeStreetAddress();
            foreach ($billingAddress->getData() as $key => $value) {
                $this->writeValue($key, $value);
            }
            // Region Code
            if ($billingAddress->getRegionId() !== NULL && $this->fieldLoadingRequired('region_code')) {
                $this->writeValue('region_code', $billingAddress->getRegionModel()->getCode());
            }
            // Country - ISO3, Full Name
            if ($billingAddress->getCountryId() !== NULL) {
                if ($this->fieldLoadingRequired('country_name')) $this->writeValue('country_name', Zend_Locale::getTranslation($billingAddress->getCountryId(), 'country', 'en_US'));
                if ($this->fieldLoadingRequired('country_iso3')) $this->writeValue('country_iso3', $billingAddress->getCountryModel()->getIso3Code());
            }
        }

        // Shipping Address
        $this->_writeArray = & $returnArray['shipping']; // Write on billing level
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getId()) {
            $shippingAddress->explodeStreetAddress();
            foreach ($shippingAddress->getData() as $key => $value) {
                $this->writeValue($key, $value);
            }
            // Region Code
            if ($shippingAddress->getRegionId() !== NULL && $this->fieldLoadingRequired('region_code')) {
                $this->writeValue('region_code', $shippingAddress->getRegionModel()->getCode());
            }
            // Country - ISO3, Full Name
            if ($shippingAddress->getCountryId() !== NULL) {
                if ($this->fieldLoadingRequired('country_name')) $this->writeValue('country_name', Zend_Locale::getTranslation($shippingAddress->getCountryId(), 'country', 'en_US'));
                if ($this->fieldLoadingRequired('country_iso3')) $this->writeValue('country_iso3', $shippingAddress->getCountryModel()->getIso3Code());
            }
            // Split street into street, housenumber, add.. needs to be fixed/reworked.
            /*$streetSplit = explode(" ", preg_replace("/[[:blank:]]+/u", " ", $shippingAddress->getStreet1()));
            if (count($streetSplit) > 0) {
                $streetName = str_replace($streetSplit[count($streetSplit) - 1], '', $shippingAddress->getStreet1());
                $streetLast = preg_replace('/[^A-Za-z0-9]/', '', $streetSplit[count($streetSplit) - 1]);
                if (is_numeric($streetLast)) {
                    $streetAdd = '';
                    $streetNumber = $streetLast;
                } else {
                    $streetAdd = $streetLast[count($streetLast)];
                    $streetNumber = intval($streetLast);
                }
                $this->writeValue('street_first', trim($streetName));
                $this->writeValue('street_number', $streetNumber);
                $this->writeValue('street_add', $streetAdd);
            }*/
        }

        // Done
        return $returnArray;
    }
}