<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-07-25T18:11:28+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Order/General.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Order_General extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'General order information',
            'category' => 'Order',
            'description' => 'Export extended order information from the sales_flat_order table.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        // Fetch fields to export
        $order = $collectionItem->getOrder();
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_ORDER) {
            $this->_writeArray = & $returnArray; // Write directly on order level
        } else {
            $this->_writeArray = & $returnArray['order']; // Write on a subnode so the order details can be accessed for invoices/shipments/credit memos
            // Timestamps of creation/update
            if ($this->fieldLoadingRequired('created_at_timestamp')) $this->writeValue('created_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($order->getCreatedAt()));
            if ($this->fieldLoadingRequired('updated_at_timestamp')) $this->writeValue('updated_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($order->getUpdatedAt()));
            $this->writeValue('entity_id', $order->getEntityId());
        }

        // Nicer store name
        $this->writeValue('store_name_orig', $order->getStoreName());
        $this->writeValue('store_name', preg_replace('/[^A-Za-z0-9- ]/', ' - ', $order->getStoreName()));

        // General order data
        foreach ($order->getData() as $key => $value) {
            if ($key == 'entity_id' || $key == 'store_name') {
                continue;
            }
            $this->writeValue($key, $value);
        }

        // Sample code to export data from a 3rd party table
        /*if ($order->getId()) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            //$tableName = $resource->getTableName('catalog/product');
            $query = 'SELECT admin_name FROM salesrep WHERE order_id = ' . (int)$order->getId() . ' LIMIT 1';
            $adminUser = $readConnection->fetchOne($query);
            $this->writeValue('admin_name', $adminUser);
        }*/

        // Last invoice, shipment, credit memo ID
        if ($order->hasInvoices() && ($this->fieldLoadingRequired('invoice_increment_id') || $this->fieldLoadingRequired('invoice_created_at_timestamp'))) {
            $invoiceCollection = $order->getInvoiceCollection();
            if (!empty($invoiceCollection)) {
                $lastInvoice = $invoiceCollection->getLastItem();
                $this->writeValue('invoice_increment_id', $lastInvoice->getIncrementId());
                $this->writeValue('invoice_created_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($lastInvoice->getCreatedAt()));
            }
        }
        if ($order->hasShipments() && ($this->fieldLoadingRequired('shipment_increment_id') || $this->fieldLoadingRequired('shipment_created_at_timestamp'))) {
            $shipmentCollection = $order->getShipmentsCollection();
            if (!empty($shipmentCollection)) {
                $lastShipment = $shipmentCollection->getLastItem();
                $this->writeValue('shipment_increment_id', $lastShipment->getIncrementId());
                $this->writeValue('shipment_created_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($lastShipment->getCreatedAt()));
            }
        }
        if ($order->hasCreditmemos() && ($this->fieldLoadingRequired('creditmemo_increment_id') || $this->fieldLoadingRequired('creditmemo_created_at_timestamp'))) {
            $creditmemoCollection = $order->getCreditmemosCollection();
            if (!empty($creditmemoCollection)) {
                $lastCreditmemo = $creditmemoCollection->getLastItem();
                $this->writeValue('creditmemo_increment_id', $lastCreditmemo->getIncrementId());
                $this->writeValue('creditmemo_created_at_timestamp', Mage::helper('xtento_orderexport/date')->convertDateToStoreTimestamp($lastCreditmemo->getCreatedAt()));
            }
        }

        // Gift message
        if ($order->getGiftMessageId() && $this->fieldLoadingRequired('gift_message')) {
            $giftMessageModel = Mage::getModel('giftmessage/message')->load($order->getGiftMessageId());
            if ($giftMessageModel->getId()) {
                $this->writeValue('gift_message_sender', $giftMessageModel->getSender());
                $this->writeValue('gift_message_recipient', $giftMessageModel->getRecipient());
                $this->writeValue('gift_message', $giftMessageModel->getMessage());
            }
        } else {
            $this->writeValue('gift_message_sender', '');
            $this->writeValue('gift_message_recipient', '');
            $this->writeValue('gift_message', '');
        }

        // Done
        return $returnArray;
    }
}