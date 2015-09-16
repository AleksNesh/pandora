<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-05-21T16:42:33+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Shared/Customer.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Shared_Customer extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        // Init cache
        if (!isset($this->_cache['customer_group'])) {
            $this->_cache['customer_group'] = array();
        }
        // Return config
        return array(
            'name' => 'Customer information',
            'category' => 'Customer',
            'description' => 'Export customer information from customer tables.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO, Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        // Fetch fields to export
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
            $customer = Mage::getModel('customer/customer')->load($collectionItem->getObject()->getId());
            $this->_writeArray = & $returnArray; // Write on main level
        } else {
            $this->_writeArray = & $returnArray['customer']; // Write on customer level
            $order = $collectionItem->getOrder();
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            if (!$customer || !$customer->getId()) {
                if ($this->getShowEmptyFields()) { // If this is debug mode and no customer was found, still output the customer attribute codes
                    $collection = Mage::getResourceModel('customer/customer_collection')
                        ->addAttributeToSelect('*');
                    $collection->getSelect()->limit(1, 0); // At least one customer must exist for this to work
                    if ($customer = $collection->getFirstItem()) {
                        foreach ($customer->getData() as $key => $value) {
                            $this->writeValue($key, NULL);
                        }
                    }
                }
                return $returnArray;
            }
        }

        if ($entityType !== Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER && !$this->fieldLoadingRequired('customer')) {
            return $returnArray;
        }

        // Customer data
        foreach ($customer->getData() as $key => $value) {
            $this->writeValue($key, $value);
        }

        // Customer group
        if ($this->fieldLoadingRequired('customer_group')) {
            if (isset($this->_cache['customer_group'][$customer->getGroupId()])) {
            $this->writeValue('customer_group', $this->_cache['customer_group'][$customer->getGroupId()]);
        } else {
            $customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId());
            if ($customerGroup && $customerGroup->getId()) {
                $this->writeValue('customer_group', $customerGroup->getCustomerGroupCode());
                $this->_cache['customer_group'][$customer->getGroupId()] = $customerGroup->getCustomerGroupCode();
            }
        }
        }

        // Timestamps of creation/update
        if ($this->fieldLoadingRequired('created_at_timestamp')) $this->writeValue('created_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($customer->getCreatedAt()));
        if ($this->fieldLoadingRequired('updated_at_timestamp')) $this->writeValue('updated_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($customer->getUpdatedAt()));

        // Customer addresses
        $addressCollection = $customer->getAddressesCollection();
        if (!empty($addressCollection) && $this->fieldLoadingRequired('addresses')) {
            foreach ($addressCollection as $customerAddress) {
                if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
                    $this->_writeArray = & $returnArray['addresses'][];
                } else {
                    $this->_writeArray = & $returnArray['customer']['addresses'][];
                }
                foreach ($customerAddress->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
            }
        }

        // Is subscribed to newsletter
        if ($customer && $this->fieldLoadingRequired('is_subscribed')) {
            $subscription = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
            if ($subscription->getId()) {
                $this->writeValue('is_subscribed', $subscription->isSubscribed());
            }
        }

        // Done
        return $returnArray;
    }
}