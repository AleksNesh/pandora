<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2012-11-10T16:36:05+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/System/Config/Backend/Export/Servername.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_System_Config_Backend_Export_Servername extends Mage_Core_Model_Config_Data
{

    public function afterLoad()
    {
        $sName1 = Mage::getModel('xtento_orderexport/system_config_backend_export_server')->getFirstName();
        $sName2 = Mage::getModel('xtento_orderexport/system_config_backend_export_server')->getSecondName();
        if ($sName1 !== $sName2) {
            $this->setValue(sprintf('%s (Main: %s)', $sName1, $sName2));
        } else {
            $this->setValue(sprintf('%s', $sName1));
        }
    }

}