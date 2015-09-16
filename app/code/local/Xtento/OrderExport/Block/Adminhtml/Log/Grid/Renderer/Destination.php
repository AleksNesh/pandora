<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-02-09T23:24:36+01:00
 * File:          app/code/local/Xtento/OrderExport/Block/Adminhtml/Log/Grid/Renderer/Destination.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Block_Adminhtml_Log_Grid_Renderer_Destination extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    static $destinations = array();

    public function render(Varien_Object $row)
    {
        $destinationIds = $row->getDestinationIds();
        $destinationText = "";
        if (empty($destinationIds)) {
            return Mage::helper('xtento_orderexport')->__('No destination saved.');
        }
        foreach (explode("&", $destinationIds) as $destinationId) {
            if (!empty($destinationId) && is_numeric($destinationId)) {
                if (!isset(self::$destinations[$destinationId])) {
                    $destination = Mage::getModel('xtento_orderexport/destination')->load($destinationId);
                    self::$destinations[$destinationId] = $destination;
                } else {
                    $destination = self::$destinations[$destinationId];
                }
                if ($destination->getId()) {
                    $destinationText .= $destination->getName() . " (".Mage::getSingleton('xtento_orderexport/system_config_source_destination_type')->getName($destination->getType()).")<br>";
                }
            }
        }
        return $destinationText;
    }
}