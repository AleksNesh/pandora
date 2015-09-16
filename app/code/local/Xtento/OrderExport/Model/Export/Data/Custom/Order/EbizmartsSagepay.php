<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-03-30T18:52:31+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Custom/Order/EbizmartsSagepay.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Custom_Order_EbizmartsSagepay extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'SagePaySuite Payment Data',
            'category' => 'Order Payment',
            'description' => 'Export transaction data from ebizmarts SagePaySuite',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
            'depends_module' => 'Ebizmarts_SagePaySuite',
            'third_party' => true
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        $this->_writeArray = & $returnArray['payment']['sagepay'];

        if (!$this->fieldLoadingRequired('sagepay')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();
        $orderTx = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->loadByParent($order->getId());
        if ($orderTx->getId()) {
            foreach ($orderTx->getData() as $key => $value) {
                $this->writeValue($key, $value);
            }
        }
        return $returnArray;
    }
}