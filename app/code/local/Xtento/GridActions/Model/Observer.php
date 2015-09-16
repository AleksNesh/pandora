<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2014-01-07T15:59:07+01:00
 * File:          app/code/local/Xtento/GridActions/Model/Observer.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Model_Observer
{
    const MODULE_ENABLED = 'gridactions/general/enabled';

    /**
     * Add required javascript modification to admin
     * @param type $observer
     */
    public function core_block_abstract_prepare_layout_after_javascript_init($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (in_array($block->getRequest()->getControllerName(), $this->getControllerNames())) {
            if (($block instanceof Mage_Adminhtml_Block_Widget_Grid || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid) && $block->getId() == 'sales_order_grid' && Mage::helper('gridactions/data')->getModuleEnabled()) {
                // Load required javascript grid modification.
                if ($block->getLayout()->getBlock('head')) {
                    $block->getLayout()->getBlock('head')->addJs('xtento/adminhtml_grid.js');
                }
            }
        }
    }

    /**
     * Add mass-actions to the sales order grid, the non-intrusive way.
     * @param type $observer
     */
    public function core_block_abstract_prepare_layout_after($observer)
    {
        $block = $observer->getEvent()->getBlock();

        #Mage::log('XTENTO - Controller name is: '.$block->getRequest()->getControllerName(), null, '', true);
        if (in_array($block->getRequest()->getControllerName(), $this->getControllerNames())) {
            $isSecure = Mage::app()->getStore()->isCurrentlySecure() ? true : false;
            if ($block->getRequest()->getActionName() !== 'exportCsv' && $block->getRequest()->getActionName() !== 'exportExcel') { // Do not add columns if admin is exporting orders using the built-in Magento CSV/Excel XML export - you don't want the carrier dropdown/select there
                if (($block instanceof Mage_Adminhtml_Block_Widget_Grid || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid) && $block->getId() == 'sales_order_grid') {
                    if (Mage::getStoreConfigFlag('gridactions/general/add_trackingnumber_from_grid') && Mage::helper('gridactions')->getModuleEnabled()) {
                        // Add tracking & carrier fields to grid
                        if (Mage::getStoreConfigFlag('gridactions/general/add_trackingnumber_from_grid_combined')) {
                            $block->addColumn(
                                'combined-input',
                                array('header' => Mage::helper('gridactions')->__('Carrier / Tracking'),
                                    'type' => 'text',
                                    'sortable' => false,
                                    'renderer' => 'Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Combined',
                                    'filter' => 'Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Combined',
                                    'width' => 190)
                            );
                        } else {
                            $block->addColumn(
                                'carrier-selector',
                                array('header' => Mage::helper('gridactions')->__('Shipping Carrier'),
                                    'type' => 'text',
                                    'sortable' => false,
                                    'renderer' => 'Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Carrier',
                                    'filter' => 'Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Carrier',
                                    'width' => 190)
                            );
                            $block->addColumn(
                                'tracking-input',
                                array('header' => Mage::helper('gridactions')->__('Tracking Number'),
                                    'type' => 'text',
                                    'sortable' => false,
                                    'renderer' => 'Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Trackingnumber',
                                    'filter' => 'Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Trackingnumber',
                                    'width' => 170,
                                    'after' => 'carrier-selector')
                            );
                        }
                    }
                }
            }

            if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction) {
                if (!$this->_initBlocks($block)) {
                    return;
                }
                if (Mage::registry('moduleString') !== 'false') {
                    return;
                }
                $enabledActions = explode(",", Mage::getStoreConfig('gridactions/general/actions'));
                // Add mass-actions to the sales order grid
                $actions = Mage::getModel('gridactions/system_config_source_actions')->toOptionArray();
                foreach ($actions as $action) {
                    $actionCode = $action['value'];
                    $actionName = $action['label'];

                    if (!in_array($actionCode, $enabledActions) && isset($enabledActions[0]) && $enabledActions[0] !== 'all') {
                        continue;
                    }
                    if ($this->_isAllowed($actionCode)) {
                        $block->addItem($actionCode, array(
                            'label' => Mage::helper('gridactions')->__($actionName),
                            'url' => Mage::app()->getStore()->getUrl('*/gridactions_grid/mass', array('actions' => $actionCode, '_secure' => $isSecure)),
                            'selected' => ($actionCode == 'invoice') ? true : false,
                            #'confirm' => Mage::helper('adminhtml')->__('Are you sure?')
                        ));
                    }
                }
            }
        }
    }

    private function _isAllowed($actionCode)
    {
        if (stristr($actionCode, 'invoice') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/invoice')) {
            return false;
        }
        if (stristr($actionCode, 'ship') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/ship')) {
            return false;
        }
        if (stristr($actionCode, 'capture') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/capture')) {
            return false;
        }
        if (stristr($actionCode, 'print') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/print')) {
            return false;
        }
        if (stristr($actionCode, 'complete') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/complete')) {
            return false;
        }
        if (stristr($actionCode, 'notify') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/email')) {
            return false;
        }
        if (stristr($actionCode, 'setstatus') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/change_status')) {
            return false;
        }
        if (stristr($actionCode, 'delete') && !Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions/delete')) {
            return false;
        }
        return true;
    }

    /*
     * Remove old grid actions, if the old module is still installed.
     */
    private function _removeOldGridActions($block)
    {
        if ($block->getItem('complete_without') !== null) {
            $block->removeItem('delete');
            $block->removeItem('invoice');
            $block->removeItem('invoice_without');
            $block->removeItem('ship');
            $block->removeItem('ship_without');
            $block->removeItem('invoice_ship');
            $block->removeItem('invoice_ship_without');
            $block->removeItem('invoice_ship_complete');
            $block->removeItem('invoice_ship_complete_without');
            $block->removeItem('complete_without');
        }
    }

    private function _initBlocks($block)
    {
        if (!Mage::helper('gridactions/data')->getModuleEnabled()) {
            return false;
        }
        $this->_removeOldGridActions($block);
        return true;
    }

    /*
     * Get controller names where the module is supposed to modify the block
     */
    public function getControllerNames()
    {
        return array('order', 'sales_order', 'adminhtml_sales_order', 'admin_sales_order', 'orderspro_order');
    }

    public function controller_action_predispatch_adminhtml($event)
    {
        // Check if this module was made for the edition (CE/PE/EE) it's being run in
        $controller = $event->getControllerAction();
        if ((in_array($controller->getRequest()->getControllerName(), $this->getControllerNames()) && $controller->getRequest()->getActionName() == 'index') || ($controller->getRequest()->getControllerName() == 'system_config' && $controller->getRequest()->getParam('section') == 'gridactions')) {
            if (!Mage::registry('edition_warning_shown')) {
                if (Xtento_GridActions_Helper_Data::EDITION !== 'CE' && Xtento_GridActions_Helper_Data::EDITION !== '') {
                    if (Mage::helper('xtcore/utils')->getIsPEorEE() && Mage::helper('gridactions')->getModuleEnabled()) {
                        if (Xtento_GridActions_Helper_Data::EDITION !== 'EE') {
                            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xtcore')->__('Attention: The installed Simplify Bulk Order Processing version is not compatible with the Enterprise Edition of Magento. The compatibility of the currently installed extension version has only been confirmed with the Community Edition of Magento. Please go to <a href="https://www.xtento.com" target="_blank">www.xtento.com</a> to purchase or download the Enterprise Edition of this extension in our store if you\'ve already purchased it.'));
                        }
                    }
                }
                Mage::register('edition_warning_shown', true);
            }
        }
    }
}