<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-03-30T18:57:33+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Order/History.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Order_History extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Order Status History',
            'category' => 'Order',
            'description' => 'Export the order status history and any comments added.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        $this->_writeArray = & $returnArray['order_status_history'];
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('order_status_history')) {
            return $returnArray;
        }

        if ($order) {
            foreach ($order->getAllStatusHistory() as $history) {
                $this->_writeArray = & $returnArray['order_status_history'][];
                $this->writeValue('comment', $history->getComment());
                $this->writeValue('created_at', $history->getCreatedAtDate());
                $this->writeValue('status', $history->getStatus());
                $this->writeValue('status_label', $history->getStatusLabel());
            }
        }
        $this->_writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}