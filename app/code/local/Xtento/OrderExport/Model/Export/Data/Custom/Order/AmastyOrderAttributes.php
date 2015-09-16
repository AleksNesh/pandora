<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-03-30T18:52:07+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Custom/Order/AmastyOrderAttributes.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Custom_Order_AmastyOrderAttributes extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    public function getConfiguration()
    {
        return array(
            'name' => 'Amasty Order Attributes Export',
            'category' => 'Order',
            'description' => 'Export custom order attributes of Amasty Order Attributes extension',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO),
            'third_party' => true,
            'depends_module' => 'Amasty_Orderattr',
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        $this->_writeArray = & $returnArray['amasty_orderattributes']; // Write on "amasty_orderattributes" level
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('amasty_orderattributes')) {
            return $returnArray;
        }

        try {
            $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
            $attributeCollection = Mage::getModel('eav/entity_attribute')->getCollection();
            $attributeCollection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
            $attributes = $attributeCollection->load();

            if ($attributes->getSize()) {
                foreach ($attributes as $attribute) {
                    if (!$this->fieldLoadingRequired($attribute->getAttributeCode())) {
                        continue;
                    }
                    $value = '';
                    switch ($attribute->getFrontendInput()) {
                        case 'select':
                            $options = $attribute->getSource()->getAllOptions(true, true);
                            foreach ($options as $option) {
                                if ($option['value'] == $orderAttributes->getData($attribute->getAttributeCode())) {
                                    $value = $option['label'];
                                    break;
                                }
                            }
                            break;
                        default:
                            $value = $orderAttributes->getData($attribute->getAttributeCode());
                            break;
                    }
                    $this->writeValue($attribute->getAttributeCode(), $value);
                }
            }
        } catch (Exception $e) {

        }

        // Done
        return $returnArray;
    }
}