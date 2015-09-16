<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-01-08T21:27:21+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Entity/Collection/Item.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Entity_Collection_Item extends Varien_Object
{
    private $_collectionItem;

    public function __construct($collectionItem, $entityType, $currItemNo, $collectionCount)
    {
        $this->_collectionItem = $collectionItem;
        $this->_collectionSize = $collectionCount;
        $this->_currItemNo = $currItemNo;
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_ORDER) {
            $this->setOrder($collectionItem);
        }
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_INVOICE) {
            $this->setOrder($collectionItem->getOrder());
            $this->setInvoice($collectionItem);
        }
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT) {
            $this->setOrder($collectionItem->getOrder());
            $this->setShipment($collectionItem);
        }
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO) {
            $this->setOrder($collectionItem->getOrder());
            $this->setCreditmemo($collectionItem);
        }
        if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_QUOTE) {
            $this->setOrder($collectionItem);
        }
    }

    public function getObject() {
        return $this->_collectionItem;
    }
}