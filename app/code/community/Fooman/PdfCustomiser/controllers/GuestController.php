<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once BP . '/app/code/core/Mage/Sales/controllers/GuestController.php';

class Fooman_PdfCustomiser_GuestController extends Mage_Sales_GuestController
{

    //duplication of OrderController necessary because of preDispatch()

    protected function _canViewOrder($order)
    {
        if (!Mage::helper('sales/guest')->loadValidOrder()) {
            return false;
        }

        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        $currentOrder = Mage::registry('current_order');

        if ($order->getId() && ($order->getId() === $currentOrder->getId())
            && in_array($order->getState(), $availableStates, true)
        ) {
            return true;
        }
        return false;
    }

    /**
     * render pdf of current order and send as pdf to browser
     *
     * @param void
     */
    public function printAction()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::getModel('pdfcustomiser/order')->renderPdf(array($order));
        } else {
            $this->_redirect('*/*/view');
        }
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
                Mage::getModel('pdfcustomiser/invoice')->renderPdf(array($invoice));
            } else {
                $this->_redirect('*/*/history');
            }
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, array($orderId));
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
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(array($shipment));
            } else {
                $this->_redirect('*/*/history');
            }
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, array($orderId));
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
                Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(array($creditmemo));
            } else {
                $this->_redirect('*/*/history');
            }
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_canViewOrder($order)) {
                Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(null, array($orderId));
            } else {
                $this->_redirect('*/*/history');
            }
        }
    }


}
