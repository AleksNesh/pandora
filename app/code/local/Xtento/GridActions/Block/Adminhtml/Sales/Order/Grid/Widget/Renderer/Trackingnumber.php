<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2013-10-27T20:24:23+01:00
 * File:          app/code/local/Xtento/GridActions/Block/Adminhtml/Sales/Order/Grid/Widget/Renderer/Trackingnumber.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Trackingnumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    protected $_column = false;

    public function render(Varien_Object $row)
    {
        if ($this->_column !== false) {
            $column = $this->_column;
        } else {
            $column = $this->getColumn();
        }
        #$colId = $column->getName() ? $column->getName() : $column->getId();
        $colId = 'tracking-input';
        $orderId = $row->getEntityId();
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            return '';
        }

        $html = '';
        if ($order->canShip()) {
            $html = '<input name="' . $colId . '-' . $row->getId() . '" rel="' . $row->getId() . '" class="input-text ' . $colId . '" value="' . $row->getData($column->getIndex()) . '" style="width:97%;" onclick="xtentoOnClickJs(this)"/>';
        } else if (!$order->canShip() && $order->getStatus() !== Mage_Sales_Model_Order::STATE_CANCELED && $order->getStatus() !== Mage_Sales_Model_Order::STATE_CLOSED) {
            $trackingNumbers = array();
            if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.4.0.0', '>=')) {
                $trackingUrl = Mage::helper('shipping')->getTrackingPopupUrlBySalesModel($order);
            } else {
                $trackingUrl = Mage::helper('shipping')->getTrackingPopUpUrlByOrderId($order->getEntityId());
            }
            // Starting from Magento 1.6, the trackingnumber field has been renamed from number to track_number
            if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.6.0.0', '>=')) {
                $tracks = Mage::getModel('sales/order_shipment_track')
                    ->getCollection()
                    ->addAttributeToSelect('track_number')
                    ->setOrderFilter($row->getEntityId());
                foreach ($tracks as $track) {
                    $trackingNumbers[] = '<a href="#" onclick="popWin(\'' . $trackingUrl . '\',\'trackorder\',\'width=800,height=600,left=0,top=0,resizable=yes,scrollbars=yes\')" >' . $this->escapeHtml($track->getTrackNumber()) . '</a>';
                }
            } else {
                $tracks = Mage::getModel('sales/order_shipment_track')
                    ->getCollection()
                    ->addAttributeToSelect('number')
                    ->setOrderFilter($row->getEntityId());
                foreach ($tracks as $track) {
                    $trackingNumbers[] = '<a href="#" onclick="popWin(\'' . $trackingUrl . '\',\'trackorder\',\'width=800,height=600,left=0,top=0,resizable=yes,scrollbars=yes\')" >' . $this->escapeHtml($track->getNumber()) . '</a>';
                }
            }
            $html = implode(', ', $trackingNumbers);

            if (Mage::getStoreConfigFlag('gridactions/general/add_trackingnumber_from_grid_shipped')) {
                if (count($tracks) > 0) {
                    $html .= '<br/>';
                }
                $html .= '<input name="' . $colId . '-' . $row->getId() . '" rel="' . $row->getId() . '" class="input-text ' . $colId . '"
                            value="' . $row->getData($column->getIndex()) . '" style="width:97%;" onclick="xtentoOnClickJs(this)"/>';
            }
        }

        return $html;
    }

    public function renderCombined($row, $column)
    {
        $this->_column = $column;
        return $this->render($row);
    }

    /*
     * Return dummy filter.
     */
    public function getFilter()
    {
        return false;
    }

    /* Fix for compatibility with Magento version <1.4 */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.4.0.0', '>=')) {
            return Mage::helper('core')->escapeHtml($data, $allowedTags);
        } else {
            return Mage::helper('core')->htmlEscape($data, $allowedTags);
        }
    }

}
