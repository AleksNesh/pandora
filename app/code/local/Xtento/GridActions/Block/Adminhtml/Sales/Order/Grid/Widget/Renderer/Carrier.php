<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2013-07-01T16:25:26+02:00
 * File:          app/code/local/Xtento/GridActions/Block/Adminhtml/Sales/Order/Grid/Widget/Renderer/Carrier.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Carrier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    protected $_column = false;

    public function render(Varien_Object $row)
    {
        $html = '';

        $orderId = $row->getEntityId();
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            return $html;
        }

        if (!$order->canShip() && $order->getStatus() !== Mage_Sales_Model_Order::STATE_CANCELED && $order->getStatus() !== Mage_Sales_Model_Order::STATE_CLOSED) {
            // Order has been shipped. Display shipped carriers.
            $carriers = array();
            $tracks = Mage::getModel('sales/order_shipment_track')
                ->getCollection()
                ->addAttributeToSelect('title')
                ->setOrderFilter($row->getEntityId());
            foreach ($tracks as $track) {
                $carriers[] = $track->getTitle();
            }
            $html = implode(', ', $carriers);

            if (Mage::getStoreConfigFlag('gridactions/general/add_trackingnumber_from_grid_shipped')) {
                if (count($tracks) > 0) {
                    $html .= '<br/>';
                }
                $html .= $this->getCarrierDropdown($row, $order);
            }
        } else if ($order->canShip()) {
            // Order has not yet been shipped. Display drop down.
            $html .= $this->getCarrierDropdown($row, $order);
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

    private function getCarrierDropdown($row, $order)
    {
        $html = '';
        try {
            $validCarriers = Mage::getModel('sales/order_shipment_api')->getCarriers($order->getIncrementId());
            if ($validCarriers) {
                #if ($this->_column !== false) {
                #    $colId = $this->_column->getName() ? $this->_column->getName() : $this->_column->getId();
                #} else {
                #    $colId = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
                #}
                $colId = 'carrier-selector';
                $html = '<select name="' . $colId . '-' . $row->getId() . '" rel="' . $row->getId() . '" class="' . $colId . '" style="width: 100%;" onchange="xtentoOnClickJs(this)">';
                foreach ($validCarriers as $code => $label) {
                    $selected = (($code == Mage::getStoreConfig('gridactions/general/default_carrier')) ? ' selected="selected"' : '');
                    $html .= '<option ' . $selected . ' value="' . $code . '">' . $label . '</option>';
                }
                $html .= '</select>';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $html;
    }
}
