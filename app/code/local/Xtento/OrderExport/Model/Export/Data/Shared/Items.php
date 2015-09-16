<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-07-28T11:46:58+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data/Shared/Items.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data_Shared_Items extends Xtento_OrderExport_Model_Export_Data_Abstract
{
    private $_origWriteArray;

    public function getConfiguration()
    {
        // Init cache
        if (!isset($this->_cache['product_attributes'])) {
            $this->_cache['product_attributes'] = array();
        }
        // Return config
        return array(
            'name' => 'Item information',
            'category' => 'Shared',
            'description' => 'Export ordered/invoiced/shipped/refunded items of entity.',
            'enabled' => true,
            'apply_to' => array(Xtento_OrderExport_Model_Export::ENTITY_ORDER, Xtento_OrderExport_Model_Export::ENTITY_INVOICE, Xtento_OrderExport_Model_Export::ENTITY_SHIPMENT, Xtento_OrderExport_Model_Export::ENTITY_CREDITMEMO, Xtento_OrderExport_Model_Export::ENTITY_QUOTE),
        );
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = array();
        $this->_writeArray = & $returnArray['items'];
        // Fetch fields to export
        $object = $collectionItem->getObject();
        #$order = $collectionItem->getOrder();
        $items = $object->getAllItems();
        if (empty($items) || (!$this->fieldLoadingRequired('items') && !$this->fieldLoadingRequired('tax_rates'))) {
            return $returnArray;
        }

        // Export item information
        $taxRates = array();
        $itemCount = 0;
        $totalQty = 0;
        foreach ($items as $item) {
            // Check if this product type should be exported
            if ($item->getProductType() && $this->getProfile() && in_array($item->getProductType(), explode(",", $this->getProfile()->getExportFilterProductType()))) {
                continue; // Product type should be not exported
            }
            // Export general item information
            $this->_writeArray = & $returnArray['items'][];
            $this->_origWriteArray = & $this->_writeArray;
            $itemCount++;
            if ($entityType == Xtento_OrderExport_Model_Export::ENTITY_ORDER) {
                $itemQty = $item->getQtyOrdered();
                #$itemQty = round($itemQty);
            } else {
                $itemQty = $item->getQty();
                #$itemQty = round($itemQty);
            }
            $totalQty += $itemQty;
            $this->writeValue('qty_ordered', $itemQty); // Legacy
            $this->writeValue('qty', $itemQty);

            $this->writeValue('item_number', $itemCount);
            $this->writeValue('order_product_number', $itemCount); // Legacy
            foreach ($item->getData() as $key => $value) {
                if ($key == 'qty_ordered' || $key == 'qty') continue;
                $this->writeValue($key, $value);
            }

            // Add fields of order item for invoice exports
            $taxItem = false;
            if ($entityType !== Xtento_OrderExport_Model_Export::ENTITY_ORDER && $entityType !== Xtento_OrderExport_Model_Export::ENTITY_QUOTE && ($this->fieldLoadingRequired('order_item') || $this->fieldLoadingRequired('tax_rates'))) {
                $this->_writeArray['order_item'] = array();
                $this->_writeArray =& $this->_writeArray['order_item'];
                if ($item->getOrderItemId()) {
                    $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                    if ($orderItem->getId()) {
                        $taxItem = $orderItem;
                        foreach ($orderItem->getData() as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                }
            } else {
                $taxItem = $item;
            }

            if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE && $this->fieldLoadingRequired('downloadable_links')) {
                $productOptions = $item->getProductOptions();
                if ($productOptions) {
                    if (isset($productOptions['links']) && is_array($productOptions['links'])) {
                        $this->_writeArray['downloadable_links'] = array();
                        $downloadableLinksCollection = Mage::getModel('downloadable/link')->getCollection()
                            ->addTitleToResult()
                            ->addFieldToFilter('`main_table`.link_id', array('in' => $productOptions['links']));
                        foreach ($downloadableLinksCollection as $downloadableLink) {
                            $this->_writeArray = & $this->_origWriteArray['downloadable_links'][];
                            foreach ($downloadableLink->getData() as $downloadableKey => $downloadableValue) {
                                $this->writeValue($downloadableKey, $downloadableValue);
                            }
                        }
                        $this->_writeArray = & $this->_origWriteArray;
                    }
                }
            }

            // Save tax information for order
            if ($taxItem && $taxItem->getTaxAmount() > 0 && $taxItem->getTaxPercent() > 0) {
                $taxPercent = str_replace('.', '_', sprintf('%.4f', $taxItem->getTaxPercent()));
                if (!isset($taxRates[$taxPercent])) {
                    $taxRates[$taxPercent] = $taxItem->getTaxAmount();
                } else {
                    $taxRates[$taxPercent] += $taxItem->getTaxAmount();
                }
            }

            // Add fields of parent item
            if ($this->fieldLoadingRequired('parent_item') && $item->getParentItem()) {
                $this->_writeArray['parent_item'] = array();
                $this->_writeArray =& $this->_writeArray['parent_item'];
                $tempOrigArray = & $this->_writeArray;
                foreach ($item->getParentItem()->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                // Export parent product options
                if ($this->fieldLoadingRequired('custom_options') && $options = $item->getParentItem()->getProductOptions()) {
                    $this->_writeCustomOptions($options, $this->_writeArray);
                }
                $this->_writeArray =& $tempOrigArray;
                if ($this->fieldLoadingRequired('product_attributes')) {
                    $this->_writeProductAttributes($object, $item->getParentItem());
                }
            }
            $this->_writeArray = & $this->_origWriteArray;
            // @todo: add "child field" (of this data type) so product attribute export can be disabled to speed up the export
            // Export product attributes
            if ($this->fieldLoadingRequired('product_attributes')) {
                $this->_writeProductAttributes($object, $item);
            }

            $this->_writeArray = & $this->_origWriteArray;
            // Export product options
            if ($this->fieldLoadingRequired('custom_options') && $options = $item->getProductOptions()) {
                // Export custom options
                $this->_writeCustomOptions($options, $this->_origWriteArray);
                // Export $options["attributes_info"].. maybe?
            }
        }
        $this->_writeArray = & $returnArray;
        $this->writeValue('export_total_qty_ordered', $totalQty);

        // Add tax amounts of other fees to $taxRates
        // Shipping
        if ($object->getShippingAmount() > 0 && $object->getShippingTaxAmount() > 0) {
            $taxPercent = round($object->getShippingTaxAmount() / $object->getShippingAmount() * 100);
            $taxPercent = str_replace('.', '_', sprintf('%.4f', $taxPercent));
            if (!isset($taxRates[$taxPercent])) {
                $taxRates[$taxPercent] = $object->getShippingTaxAmount();
            } else {
                $taxRates[$taxPercent] += $object->getShippingTaxAmount();
            }
        }
        // Cash on Delivery
        if ($object->getOrder()) {
            $codFee = $object->getOrder()->getCodFee();
            $codFeeTax = $object->getOrder()->getCodTaxAmount();
        } else {
            $codFee = $object->getCodFee();
            $codFeeTax = $object->getCodTaxAmount();
        }
        if ($codFee > 0 && $codFeeTax > 0) {
            $taxPercent = round($codFeeTax / $codFee * 100);
            $taxPercent = str_replace('.', '_', sprintf('%.4f', $taxPercent));
            if (!isset($taxRates[$taxPercent])) {
                $taxRates[$taxPercent] = $codFeeTax;
            } else {
                $taxRates[$taxPercent] += $codFeeTax;
            }
        }

        // Export tax information
        $this->_writeArray['tax_rates'] = array();
        if ($this->fieldLoadingRequired('tax_rates')) {
            foreach ($taxRates as $taxRate => $taxAmount) {
                $this->_writeArray = & $returnArray['tax_rates'][];
                $this->writeValue('rate', str_replace('_', '.', $taxRate));
                $this->writeValue('amount', $taxAmount);
            }
        }

        // Done
        return $returnArray;
    }

    private function _writeCustomOptions($options, &$writeArray)
    {
        if (isset($options['options'])) {
            $this->_writeArray['custom_options'] = array();
            foreach ($options['options'] as $customOption) {
                $optionValues = explode(",", $customOption['option_value']);
                $optionCount = 0;
                foreach ($optionValues as $optionValue) {
                    $values = Mage::getModel('catalog/product_option_value')->load($optionValue);
                    if ($values->getOptionId()) {
                        $optionCount++;
                        $this->_writeArray = & $writeArray['custom_options'][];
                        $this->writeValue('name', $customOption['label']);
                        $this->writeValue('value', $customOption['value']);
                        $this->writeValue('sku', $values->getSku());
                    }
                }
                if ($optionCount === 0) {
                    $this->_writeArray = & $writeArray['custom_options'][];
                    $this->writeValue('name', $customOption['label']);
                    $this->writeValue('value', $customOption['value']);
                    $this->writeValue('sku', '');
                }
                if (isset($customOption['option_value'])) {
                    $unserializedOptionValues = @unserialize($customOption['option_value']);
                    if ($unserializedOptionValues !== false) {
                        foreach ($unserializedOptionValues as $unserializedOptionKey => $unserializedOptionValue) {
                            $this->writeValue($unserializedOptionKey, $unserializedOptionValue);
                        }
                    }
                }
            }
        }
    }

    private function _writeProductAttributes($object, $item)
    {
        $this->_writeArray['product_attributes'] = array();
        $this->_writeArray = & $this->_writeArray['product_attributes'];
        if (isset($this->_cache['product_attributes'][$object->getStoreId()]) && isset($this->_cache['product_attributes'][$object->getStoreId()][$item->getProductId()])) {
            // "cached"
            foreach ($this->_cache['product_attributes'][$object->getStoreId()][$item->getProductId()] as $attributeCode => $value) {
                $this->writeValue($attributeCode, $value);
            }
        } else {
            $product = Mage::getModel('catalog/product')->setStoreId($object->getStoreId())->load($item->getProductId());
            if ($product->getId()) {
                foreach ($product->getAttributes(null, true) as $productAttribute) {
                    $attributeCode = $productAttribute->getAttributeCode();
                    if (!$this->fieldLoadingRequired($attributeCode) || $attributeCode == 'category_ids') {
                        continue;
                    }
                    $value = $productAttribute->getFrontend()->getValue($product);
                    if ($attributeCode == 'image' || $attributeCode == 'small_image' || $attributeCode == 'thumbnail') {
                        $this->writeValue($attributeCode . '_raw', $value);
                        $this->writeValue($attributeCode, Mage::getBaseUrl('media') . 'catalog/product/' . $value);
                        continue;
                    }
                    $this->writeValue($attributeCode, $value);
                    $this->_cache['product_attributes'][$object->getStoreId()][$item->getProductId()][$productAttribute->getAttributeCode()] = $value;
                    // Handle attribute set name
                    if ($productAttribute->getAttributeCode() == 'attribute_set_id') {
                        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                        $attributeSetModel->load($value);
                        if ($attributeSetModel->getId()) {
                            $this->writeValue('attribute_set_name', $attributeSetModel->getAttributeSetName());
                            $this->_cache['product_attributes'][$object->getStoreId()][$item->getProductId()]['attribute_set_name'] = $attributeSetModel->getAttributeSetName();
                        }
                    }
                }
                if ($this->fieldLoadingRequired('category_ids')) {
                    $categoryIds = "|" . implode("|", $product->getCategoryIds()) . "|";
                    $this->writeValue('category_ids', $categoryIds);
                    $this->_cache['product_attributes'][$object->getStoreId()][$item->getProductId()]['category_ids'] = $categoryIds;
                }
            }
        }
    }
}