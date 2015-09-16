<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-02-09T13:08:55+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Condition/Product/Found.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Condition_Product_Found extends Mage_SalesRule_Model_Rule_Condition_Product_Found
{
    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('xtento_orderexport/export_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = array('value' => 'xtento_orderexport/export_condition_product|' . $code, 'label' => $label);
            } else {
                $pAttributes[] = array('value' => 'xtento_orderexport/export_condition_product|' . $code, 'label' => $label);
            }
        }

        $itemAttributes = array();
        $customItemAttributes = Mage::getModel('xtento_orderexport/export_condition_custom')->getCustomNotMappedAttributes('_item');
        foreach ($customItemAttributes as $code => $label) {
            $itemAttributes[] = array('value' => 'xtento_orderexport/export_condition_item|' . $code, 'label' => $label);
        }

        $conditions = array(
            array('value' => 'salesrule/rule_condition_product_combine', 'label' => Mage::helper('catalog')->__('Conditions Combination')),
            array('label' => Mage::helper('catalog')->__('Cart Item Attribute'), 'value' => $iAttributes),
            array('label' => Mage::helper('catalog')->__('Product Attribute'), 'value' => $pAttributes),
            array('label' => Mage::helper('catalog')->__('Item Attribute'), 'value' => $itemAttributes),
        );
        return $conditions;
    }
}
