<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-03-30T19:00:52+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Shared/SalesRule.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Shared_SalesRule extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Sales Rules', // Thanks to Thomas Hägi!
            'category' => 'Order',
            'description' => 'Export sales rules used by the order',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
            'third_party' => false,
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        $this->_writeArray = & $returnArray['salesrules'];

        // Get order
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('salesrules')) {
            return $returnArray;
        }

        // Get applied rules
        $ruleIDs = $order->getAppliedRuleIds();
        if ($ruleIDs) {
            $ruleIDs = explode(',', $ruleIDs);
            foreach ($ruleIDs as $ruleID) {
                // Load rule object
                $rule = Mage::getModel('salesrule/rule')->load($ruleID);
                if ($rule && $rule->getId()) {
                    // Export rule
                    $this->_writeArray = & $returnArray['salesrules'][];
                    foreach ($rule->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                }
            }
        }

        // Done
        return $returnArray;
    }
}