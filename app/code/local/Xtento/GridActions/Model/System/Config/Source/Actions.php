<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2011-12-11T17:24:51+01:00
 * File:          app/code/local/Xtento/GridActions/Model/System/Config/Source/Actions.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Model_System_Config_Source_Actions
{

    public function toOptionArray()
    {
        $actions = Mage::getModel('gridactions/system_config_source_order_actions')->getOrderActions();
        # Add your own actions:
        # $actions[] = array('value' => '__value__', 'label' => 'your_label');        
        
        return $actions;
    }

}
