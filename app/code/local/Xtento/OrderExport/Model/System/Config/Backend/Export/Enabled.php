<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2012-12-08T11:57:00+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/System/Config/Backend/Export/Enabled.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_System_Config_Backend_Export_Enabled extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        Mage::register('orderexport_modify_event', true, true);
        parent::_beforeSave();
    }

    public function has_value_for_configuration_changed($observer)
    {
        if (Mage::registry('orderexport_modify_event') == true) {
            Mage::unregister('orderexport_modify_event');
            Xtento_OrderExport_Model_System_Config_Source_Order_Status::isEnabled();
        }
    }
}
