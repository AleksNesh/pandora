<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2012-12-07T18:54:41+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Entity/Invoice.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Entity_Invoice extends Xtento_OrderExport_Model_Export_Entity_Abstract
{
    protected $_entityType = Xtento_OrderExport_Model_Export::ENTITY_INVOICE;

    protected function _construct()
    {
        $collection = Mage::getResourceModel('sales/order_invoice_collection')
            ->addAttributeToSelect('*');
        $this->_collection = $collection;
        parent::_construct();
    }
}