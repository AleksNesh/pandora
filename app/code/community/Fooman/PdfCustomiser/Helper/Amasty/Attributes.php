<?php
/**
 * Helper class to retrieve Amasty Order Attributes
 * @see Amasty_Orderattr_Helper_Pdf
 */
class Fooman_PdfCustomiser_Helper_Amasty_Attributes extends Mage_Core_Helper_Abstract
{
    public function getAttributes($order, $salesObject)
    {
        $list = array();

        if ($salesObject instanceof Mage_Sales_Model_Order_Creditmemo) {
            return $list;
        }

        if ($salesObject instanceof Mage_Sales_Model_Order_Invoice
            && !Mage::getStoreConfig(
                'amorderattr/pdf/invoice'
            )
        ) {
            return $list;
        }
        if ($salesObject instanceof Mage_Sales_Model_Order_Shipment
            && !Mage::getStoreConfig(
                'amorderattr/pdf/shipment'
            )
        ) {
            return $list;
        }

        /* loading attributes */
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $attributes->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $attributes->addFieldToFilter('include_pdf', 1);
        $attributes->getSelect()->order('checkout_step');
        $attributes->getSelect()->order('sorting_order');

        if (!$attributes->getSize()) {
            return $list;
        }

        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');

        foreach ($attributes as $attribute) {
            $currentStore = $order->getStoreId();
            $storeIds = explode(',', $attribute->getData('store_ids'));
            if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds)) {
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
                case 'date':
                    $value = $orderAttributes->getData($attribute->getAttributeCode());
                    $format = Mage::app()->getLocale()->getDateTimeFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                    );
                    if ('time' == $attribute->getNote()) {
                        $value = Mage::app()->getLocale()->date(
                            $value, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false
                        )->toString($format);
                    } else {
                        $format = trim(str_replace(array('m', 'a', 'H', ':', 'h', 's'), '', $format));
                        $value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false)
                            ->toString($format);
                    }
                    break;
                case 'checkboxes':
                    $options = $attribute->getSource()->getAllOptions(true, true);
                    $checkboxValues = explode(',', $orderAttributes->getData($attribute->getAttributeCode()));
                    foreach ($options as $option) {
                        if (in_array($option['value'], $checkboxValues)) {
                            $value[] = $option['label'];
                        }
                    }
                    $value = implode(', ', $value);
                    break;
                case 'boolean':
                    $value = $orderAttributes->getData($attribute->getAttributeCode()) ? 'Yes' : 'No';
                    $value = Mage::helper('catalog')->__($value);
                    break;
                case 'textarea':
                    $text = $orderAttributes->getData($attribute->getAttributeCode());
                    $text = str_replace(array("\r\n", "\n", "\r"), '~~~', $text);
                    $value = array();
                    foreach (explode('~~~', $text) as $str) {
                        foreach (Mage::helper('core/string')->str_split($str, 120, true, true) as $part) {
                            if (empty($part)) {
                                continue;
                            }
                            $value[] = $part;
                        }
                    }
                    break;
                default:
                    $value = $orderAttributes->getData($attribute->getAttributeCode());
                    break;
            }
            if (is_array($value)) {
                $list[$attribute->getFrontendLabel()] = $value;
            } else {
                $list[$attribute->getFrontendLabel()] = str_replace('$', '\$', $value);
            }
        }

        return $list;
    }
}
