<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-02-10T16:58:29+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/System/Config/Source/Export/Status.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_System_Config_Source_Export_Status
{

    public function toOptionArray($entity)
    {
        $statuses = array();

        if ($entity == Xtento_OrderExport_Model_Export::ENTITY_ORDER) {
            $statuses = Mage::getSingleton('xtento_orderexport/system_config_source_order_status')->toOptionArray();
            array_shift($statuses); // Remove first entry.
        } else if ($entity == Xtento_OrderExport_Model_Export::ENTITY_INVOICE) {
            foreach (Mage::getModel('sales/order_invoice')->getStates() as $state => $label) {
                $statuses[] = array('value' => $state, 'label' => $label);
            }
        } else if ($entity == Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT) {

        } else if ($entity == Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO) {
            foreach (Mage::getModel('sales/order_creditmemo')->getStates() as $state => $label) {
                $statuses[] = array('value' => $state, 'label' => $label);
            }
        }

        return $statuses;
    }

    // Function to just put all status "codes" into an array.
    public function toArray($entity)
    {
        $statuses = $this->toOptionArray($entity);
        $statusArray = array();
        foreach ($statuses as $status) {
            $statusArray[$status['value']];
        }
        return $statusArray;
    }

}
