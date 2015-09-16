<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2013-03-23T18:09:53+01:00
 * File:          app/code/local/Xtento/GridActions/Block/Adminhtml/Sales/Order/Grid/Widget/MassactionEE.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_MassactionEE extends Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
{
    public function isAvailable()
    {
        /* Compatibility with Amasty extensions */
        Mage::dispatchEvent('am_grid_massaction_actions', array(
            'block' => $this,
            'page' => $this->getRequest()->getControllerName(),
        ));
        return parent::isAvailable();
    }

    public function getJavaScript()
    {
        /* Compatibility with Amasty extensions */
        $result = new Varien_Object(array(
            'js' => parent::getJavaScript(),
            'page' => $this->getRequest()->getControllerName(),
        ));
        Mage::dispatchEvent('am_grid_massaction_js', array('result' => $result));

        if (in_array($this->getRequest()->getControllerName(), Mage::getSingleton('gridactions/observer')->getControllerNames())) {
            if (Mage::helper('gridactions/data')->getModuleEnabled() && Mage::getStoreConfigFlag('gridactions/general/add_trackingnumber_from_grid')) {
                return str_replace('varienGridMassaction', 'extendedGridMassaction', $result->getJs());
            }
        }
        return $result->getJs();
    }
}
