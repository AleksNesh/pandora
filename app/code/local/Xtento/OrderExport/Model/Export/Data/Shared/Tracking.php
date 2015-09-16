<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-07-04T12:07:58+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Shared/Tracking.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Shared_Tracking extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Tracking information',
            'category' => 'Order',
            'description' => 'Export information about tracking numbers assigned to child shipments.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        $shipments = Mage::getResourceModel('sales/order_shipment_collection')
            ->addAttributeToFilter('order_id', $order->getId())
            ->load();

        foreach ($shipments as $shipment) {
            $exportClass = Mage::getSingleton('xtento_orderexport/export_data_shipment_tracking');
            $exportClass->setProfile($this->getProfile());
            $exportClass->setShowEmptyFields($this->getShowEmptyFields());
            $returnData = $exportClass->getExportData(Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, $shipment, true);
            if (is_array($returnData) && !empty($returnData)) {
                $returnArray = array_merge_recursive($returnArray, $returnData);
            }
        }
        // Done
        return $returnArray;
    }
}