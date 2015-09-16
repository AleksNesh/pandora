<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2014-05-12T12:04:59+02:00
 * File:          app/code/local/Xtento/GridActions/Model/System/Config/Source/Order/Actions.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Model_System_Config_Source_Order_Actions
{
    public function getOrderActions()
    {
        $actions[] = array('value' => '_forceorderemail', 'label' => Mage::helper('gridactions')->__('(Re-)send order email (notify Customer)'));
        $actions[] = array('value' => '_invoice_notify', 'label' => Mage::helper('gridactions')->__('Invoice (notify Customer)'));
        $actions[] = array('value' => '_invoice', 'label' => Mage::helper('gridactions')->__('Invoice (don\'t notify Customer)'));
        $actions[] = array('value' => '_invoice_forcenotification', 'label' => Mage::helper('gridactions')->__('(Re-)send invoice email (notify Customer)'));
        $actions[] = array('value' => '_invoice_notify_print', 'label' => Mage::helper('gridactions')->__('Invoice / Print (notify Customer)'));
        $actions[] = array('value' => '_invoice_print', 'label' => Mage::helper('gridactions')->__('Invoice / Print (don\'t notify Customer)'));
        $actions[] = array('value' => '_capture', 'label' => Mage::helper('gridactions')->__('Capture Payment'));
        $actions[] = array('value' => '_ship_notify', 'label' => Mage::helper('gridactions')->__('Ship (notify Customer)'));
        $actions[] = array('value' => '_ship', 'label' => Mage::helper('gridactions')->__('Ship (don\'t notify Customer)'));
        $actions[] = array('value' => '_ship_forcenotification', 'label' => Mage::helper('gridactions')->__('(Re-)send shipment email (notify Customer)'));
        $actions[] = array('value' => '_ship_notify_print', 'label' => Mage::helper('gridactions')->__('Ship / Print (notify Customer)'));
        $actions[] = array('value' => '_ship_print', 'label' => Mage::helper('gridactions')->__('Ship / Print (don\'t notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_notify', 'label' => Mage::helper('gridactions')->__('Invoice / Ship (notify Customer)'));
        $actions[] = array('value' => '_invoice_ship', 'label' => Mage::helper('gridactions')->__('Invoice / Ship (don\'t notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_notify_print', 'label' => Mage::helper('gridactions')->__('Invoice / Ship / Print (notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_print', 'label' => Mage::helper('gridactions')->__('Invoice / Ship / Print (don\'t notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_complete_notify', 'label' => Mage::helper('gridactions')->__('Invoice / Ship / Complete (notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_complete', 'label' => Mage::helper('gridactions')->__('Invoice / Ship / Complete (don\'t notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_complete_notify_print', 'label' => Mage::helper('gridactions')->__('Invoice / Ship / Complete / Print (notify Customer)'));
        $actions[] = array('value' => '_invoice_ship_complete_print', 'label' => Mage::helper('gridactions')->__('Invoice / Ship / Complete / Print (don\'t notify Customer)'));
        $actions[] = array('value' => '_complete', 'label' => Mage::helper('gridactions')->__('Complete Order'));

        // Add order status actions
        $orderStatus = Mage::getModel('gridactions/system_config_source_order_status')->toOptionArray();
        foreach ($orderStatus as $status) {
            if ($status['value'] == 'no_change') {
                continue;
            }
            $actions[] = array('value' => '_setstatus_' . $status['value'], 'label' => Mage::helper('gridactions')->__('Set Order Status to \'%s\'', Mage::helper('adminhtml')->__((string)$status['label'])));
        }

        $actions[] = array('value' => '_delete', 'label' => Mage::helper('gridactions')->__('Delete Order (canceled-only)'));
        return $actions;
    }
}