<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once BP.'/app/code/community/Fooman/EmailAttachments/controllers/Admin/OrderController.php';

class Fooman_PdfCustomiser_Adminhtml_PdfCustomiser_Sales_OrderController extends Fooman_EmailAttachments_Admin_OrderController
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    /**
     * print orders from order_ids
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfordersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_order')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        if (sizeof($orderIds)) {
            Mage::getModel('pdfcustomiser/order')->renderPdf(null, $orderIds, null, false, null,
                $forcedStoreId, $hideLogo);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

    /**
     * print invoices and shipments combined from order_ids
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfshipmentsinvoicesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_invoice')->setHideBackground($this->getRequest()->getParam('hide_background'));
            Mage::helper('pdfcustomiser/pdf_shipment')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        //separated by order
        $total = count($orderIds);
        ksort($orderIds);
        $i = 1;
        foreach ($orderIds as $orderId) {
            if ($i == 1) {
                $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(
                    null, array($orderId), null, true, null, $forcedStoreId, $hideLogo
                );
            } else {
                $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(
                    null, array($orderId), $pdf, true, null, $forcedStoreId, $hideLogo
                );
            }
            if ($i == $total) {
                $pdf = Mage::getModel('pdfcustomiser/shipment')->renderPdf(
                    null, array($orderId), $pdf, false, 'orderDocs_', $forcedStoreId, $hideLogo
                );
                $this->_redirectReferer('adminhtml/sales_order');
            } else {
                $pdf = Mage::getModel('pdfcustomiser/shipment')->renderPdf(
                    null, array($orderId), $pdf, true, null, $forcedStoreId, $hideLogo
                );
            }
            $i++;
        }
        /*
        //all invoices first, then packingslips
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, $orderIds, null, true, null, $forcedStoreId);
            Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, $orderIds, $pdf, false, 'orderDocs_', $forcedStoreId);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
        }
        $this->_redirectReferer('adminhtml/sales_order');
        */
    }

    /**
     * print based on order_id
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function printAction()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_order')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        if ($orderId) {
            Mage::getModel('pdfcustomiser/order')->renderPdf(null, array($orderId), null, false, null,
            $forcedStoreId, $hideLogo);
        } else {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

    /**
     * print based on order_id or shipment_id
     * allows printing without having first created shipments
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfshipmentAction()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $shipmentId = (int)$this->getRequest()->getParam('shipment_id');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_shipment')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        $print = false;
        $addError = false;
        if ($orderId) {
            if (!Fooman_PdfCustomiser_Model_Abstract::COMPAT_MODE) {
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, array($orderId), null, false, null,
                    $forcedStoreId, $hideLogo);
            } else {
                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(null, array($orderId), null, false, null,
                    $forcedStoreId, $hideLogo);
                $print = $pdf->render();
                if ($print) {
                    return $this->_prepareDownloadResponse(
                        'shipments_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                        $print,
                        'application/pdf'
                    );
                } else {
                    $addError = true;
                }
            }
        } elseif ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if ($shipment) {
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(array($shipment), null, null, false, null, $forcedStoreId, $hideLogo);
            } else {
                $addError = true;
            }
        } else {
            $addError = true;
        }
        if ($addError) {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

    /**
     * print invoice based on order_id or invoice_id
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfinvoiceAction()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_invoice')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        $addError = false;
        if ($orderId) {
            Mage::getModel('pdfcustomiser/order')->renderPdf(null, array($orderId), null, false, null,
                $forcedStoreId, $hideLogo);
        } elseif ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            if ($invoice) {
                Mage::getModel('pdfcustomiser/invoice')->renderPdf(array($invoice), null, null, false, null,
                    $forcedStoreId, $hideLogo);
            } else {
                $addError = true;
            }
        } else {
            $addError = true;
        }
        if ($addError) {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

    /**
     * print creditmemo based on order_id or invoice_id
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfcreditmemoAction()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $creditmemo = (int)$this->getRequest()->getParam('creditmemo_id');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_creditmemo')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        $addError = false;
        if ($orderId) {
            Mage::getModel('pdfcustomiser/order')->renderPdf(null, array($orderId), null, false, '',
                $forcedStoreId, $hideLogo);
        } elseif ($creditmemo) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemo);
            if ($creditmemo) {
                Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(array($creditmemo), null, null, false, '',
                    $forcedStoreId, $hideLogo);
            } else {
                $addError = true;
            }
        } else {
            $addError = true;
        }
        if ($addError) {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

    /**
     * override EmailAttachment behaviour to print based on order_ids
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfdocsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $forcedStoreId = $this->getRequest()->getParam('force_store_id');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_order')->setHideBackground($this->getRequest()->getParam('hide_background'));
            Mage::helper('pdfcustomiser/pdf_invoice')->setHideBackground($this->getRequest()->getParam('hide_background'));
            Mage::helper('pdfcustomiser/pdf_shipment')->setHideBackground($this->getRequest()->getParam('hide_background'));
            Mage::helper('pdfcustomiser/pdf_creditmemo')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        if (sizeof($orderIds)) {
            if (!Fooman_PdfCustomiser_Model_Abstract::COMPAT_MODE) {
                $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, $orderIds, null, true, null,
                    $forcedStoreId, $hideLogo);
                $pdf = Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, $orderIds, $pdf, true, null,
                    $forcedStoreId, $hideLogo);
                $pdf = Mage::getModel('pdfcustomiser/order')->renderPdf(null, $orderIds, $pdf, true, null,
                    $forcedStoreId, $hideLogo);
                $pdf = Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(null, $orderIds, $pdf, false, 'orderDocs_',
                    $forcedStoreId, $hideLogo);
                $this->_redirectReferer('adminhtml/sales_order');
                /*TODO: add config option to allow sorted by order printouts
                $total = count($orderIds);
                $i=1;
                foreach ($orderIds as $orderId) {
                    if ($i == 1) {
                        $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, array($orderId), null, true);
                    } else {
                        $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, array($orderId), $pdf, true);
                    }
                    $pdf = Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, array($orderId), $pdf, true);
                    $pdf = Mage::getModel('pdfcustomiser/order')->renderPdf(null, array($orderId), $pdf, true);
                    if ($i == $total) {
                        $pdf = Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(
                            null, array($orderId), $pdf, false, 'orderDocs_'
                        );
                        $this->_redirectReferer('adminhtml/sales_order');
                    } else {
                        $pdf = Mage::getModel('pdfcustomiser/creditmemo')->renderPdf(null, array($orderId), $pdf, true);
                        $this->_redirectReferer('adminhtml/sales_order');
                    }
                    $i++;
                }
                */
            } else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(null, $orderIds, null, false, null, $forcedStoreId);
                $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf(null, $orderIds, null, false, null, $forcedStoreId);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
                $pages = Mage::getModel('pdfcustomiser/order')->getPdf(null, $orderIds, null, false, null, $forcedStoreId);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
                $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf(null, $orderIds, null, false, null, $forcedStoreId);
                $pdf->pages = array_merge($pdf->pages, $pages->pages);
                return $this->_prepareDownloadResponse(
                    'orderDocs_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf',
                    $pdf->render(),
                    'application/pdf'
                );
            }
        } else {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
            $this->_redirectReferer('adminhtml/sales_order');
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

    /**
     * send picking list for given order_ids
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfpickinglistAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser_picking/pdf_order_pickingList')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        if (sizeof($orderIds)) {
            Mage::getModel('pdfcustomiser_picking/order_pickingList')->renderPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }
}
