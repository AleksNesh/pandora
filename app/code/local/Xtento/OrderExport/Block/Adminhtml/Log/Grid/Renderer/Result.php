<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-02-10T17:01:03+01:00
 * File:          app/code/local/Xtento/OrderExport/Block/Adminhtml/Log/Grid/Renderer/Result.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Block_Adminhtml_Log_Grid_Renderer_Result extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($row->getResult() === NULL || $row->getResult() == 0) {
            return '<span class="grid-severity-major"><span>' . Mage::helper('xtento_orderexport')->__('No Result') . '</span></span>';
        } else if ($row->getResult() == 1) {
            return '<span class="grid-severity-notice"><span>' . Mage::helper('xtento_orderexport')->__('Success') . '</span></span>';
        } else if ($row->getResult() == 2) {
            return '<span class="grid-severity-minor"><span>' . Mage::helper('xtento_orderexport')->__('Warning') . '</span></span>';
        } else if ($row->getResult() == 3) {
            return '<span class="grid-severity-critical"><span>' . Mage::helper('xtento_orderexport')->__('Failed') . '</span></span>';
        }
        return '';
    }
}