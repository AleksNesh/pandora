<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-06-12T17:03:38+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Custom/Order/ItabsDebitPayment.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Custom_Order_ItabsDebitPayment extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Itabs Debit Payment Data',
            'category' => 'Order Payment',
            'description' => 'Export bank account and bank number of Itabs_Debit extension',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
            'third_party' => true
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();

        if (!$this->fieldLoadingRequired('debitpayment')) {
            return $returnArray;
        }
        $payment = $collectionItem->getOrder()->getPayment();
        if ($payment->getMethod() == 'debit') {
            $this->_writeArray = & $returnArray['payment']['debitpayment'];

            // Fetch fields to export
            $this->writeValue('account_owner', $payment->getCcOwner());
            $this->writeValue('account_number', preg_replace("/[^0-9\-]/", "", Mage::helper('core')->decrypt($payment->getCcNumberEnc())));
            $this->writeValue('account_bankcode', preg_replace("/[^0-9\-]/", "", Mage::helper('core')->decrypt($payment->getCcType())));
            if (Mage::helper('xtcore/utils')->isExtensionInstalled('Itabs_Debit') || Mage::helper('xtcore/utils')->isExtensionInstalled('Mage_Debit')) {
                $this->writeValue('account_bank', Mage::helper('debit/data')->getBankByBlz(preg_replace("/[^0-9]/", "", Mage::helper('core')->decrypt($payment->getCcType()))));
            }
        }
        return $returnArray;
    }
}