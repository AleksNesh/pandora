<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
include_once('Mage/Adminhtml/controllers/Sales/OrderController.php');

class MageWorx_OrdersPro_Adminhtml_OrdersproController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Archive selected orders
     */
    public function massArchiveAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->addToOrderGroup($orderIds, 1);
        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were archived.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Delete selected orders
     */
    public function massDeleteAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->addToOrderGroup($orderIds, 2);
        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were deleted.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Delete completely selected orders
     */
    public function massDeleteCompletelyAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if (!$orderIds) {
            $orderId = $this->getRequest()->getParam('order_id', false);
            if ($orderId) $orderIds = array($orderId);
        }
        if ($orderIds) {
            $count = $this->getMwHelper()->deleteOrderCompletely($orderIds);
            if ($count == 1) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Order has been completely deleted.'));
            if ($count > 1) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were completely deleted.'));
        }
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Restore selected orders
     */
    public function massRestoreAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->addToOrderGroup($orderIds, 0);
        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were restored.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Create invoices for selected orders
     */
    public function massInvoiceAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->invoiceOrderMass($orderIds);
        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were invoiced.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Create shipments for selected orders
     */
    public function massShipAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->shipOrder($orderIds);
        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were shipped.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Create both invoice and shipment for selected orders
     */
    public function massInvoiceAndShipAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->invoiceOrderMass($orderIds);
        $count += $this->getMwHelper()->shipOrder($orderIds);
        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were invoiced and shipped.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * Delete all invoices and shipments for selected orders
     */
    public function deleteInvoiceAndShipmentAction()
    {
        $orderId = intval($this->getRequest()->getParam('order_id'));
        if ($orderId > 0) {
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_shipment') . "` WHERE `order_id` = " . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_shipment_grid') . "` WHERE `order_id` = " . $orderId);

            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_invoice') . "` WHERE `order_id` = " . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_invoice_grid') . "` WHERE `order_id` = " . $orderId);

            $write->query("UPDATE `" . $coreResource->getTableName('sales_flat_order_item') . "` SET `qty_invoiced` = 0, `qty_shipped` = 0 WHERE `order_id` = " . $orderId);
            $write->query("UPDATE `" . $coreResource->getTableName('sales_flat_order') . "` SET `shipping_invoiced` = 0, `base_shipping_invoiced` = 0 WHERE `entity_id` = " . $orderId);
        }
        $this->getResponse()->setBody('ok');
    }

    public function massInvoiceAndPrintAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = $this->getMwHelper()->invoiceOrderMass($orderIds);

        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('adminhtml/sales_order/');
            }
        }

        if ($count > 0) Mage::getSingleton('adminhtml/session')->addSuccess($this->getMwHelper()->__('Selected orders were invoiced and printed.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    /**
     * @return MageWorx_OrdersPro_Helper_Data
     */
    protected function getMwHelper()
    {
        return Mage::helper('mageworx_orderspro');
    }
}