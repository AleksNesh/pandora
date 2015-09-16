<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once BP . '/app/code/core/Mage/Sales/controllers/OrderController.php';

class Fooman_PdfCustomiser_OrderController extends Mage_Sales_OrderController
{

    /**
     * render pdf of current order and send as pdf to browser
     *
     * @param void
     */
    public function printAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::registry('current_order');
        Mage::getModel('pdfcustomiser/order')->renderPdf(array($order), null, null, false, $this->getRequest()->getParam('force_store_id'));
    }

    /**
     * render invoice and send as pdf to browser
     *
     * @param void
     */
    public function printInvoiceAction()
    {
        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $order = $invoice->getOrder();
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/invoice')->renderPdf(array($invoice), null, null, false, $this->getRequest()->getParam('force_store_id'));
            } else {
                $this->_redirect('*/*/history');
            }
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, array($orderId), null, false, $this->getRequest()->getParam('force_store_id'));
            } else {
                $this->_redirect('*/*/history');
            }
        }
    }

    /**
     * render packing slip and send as pdf to browser
     *
     * @param void
     */
    public function printShipmentAction()
    {
        $shipmentId = (int)$this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            /* @var $shipment Mage_Sales_Model_Order */
            $order = $shipment->getOrder();
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(array($shipment), null, null, false, $this->getRequest()->getParam('force_store_id'));
            } else {
                $this->_redirect('*/*/history');
            }
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, array($orderId), null, false, $this->getRequest()->getParam('force_store_id'));
            } else {
                $this->_redirect('*/*/history');
            }
        }
    }

    /**
     * render credit note and send as pdf to browser
     *
     * @param void
     */
    public function printCreditmemoAction()
    {
        $creditmemoId = (int)$this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            /* @var $creditmemo Mage_Sales_Model_Order */
            $order = $creditmemo->getOrder();
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(array($creditmemo), null, null, false, $this->getRequest()->getParam('force_store_id'));
            } else {
                $this->_redirect('*/*/history');
            }
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(null, array($orderId), null, false, $this->getRequest()->getParam('force_store_id'));
            } else {
                $this->_redirect('*/*/history');
            }
        }
    }
}