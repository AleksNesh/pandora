<?php
/**
 * Print controller
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2015 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Adminhtml_PrintController
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Adminhtml_PrintController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @param Alpine_PrintPdf_Block_Iframe $iframe
     * @param $pdf
     * @throws Exception
     */
    protected function _updateIframe(Alpine_PrintPdf_Block_Iframe $iframe, $pdf)
    {
        if ($iframe->getMethod() == Alpine_PrintPdf_Block_Iframe::METHOD_POST) {
            $helper = Mage::helper('alpine_printpdf/pdf');
            $data = $iframe->getPdfUrl() . Zend_Json::encode($iframe->getPassedParameters());
            $filename = $helper->generateFileName($data);

            $helper->saveFile($pdf, $filename);
            $iframe->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_GET);
            $iframe->setPdfUrl($this->getUrl('alpine_printpdf/print/pregenerated', array(
                'file' => $filename
            )));
        }
    }

    /**
     * Print action for single order (from Order View page)
     */
    public function orderAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        $pdfUrl = Mage::helper('adminhtml')
            ->getUrl('adminhtml/pdfCustomiser_sales_order/print', array('order_id' => $orderId));

        $this->loadLayout();

        /** @var Alpine_PrintPdf_Block_Iframe $iframe */
        $iframe = $this->getLayout()->getBlock('iframe');

        if ($iframe) {
            $iframe
                ->setPdfUrl($pdfUrl)
                ->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_GET)
                ->setPrinter(Mage::getStoreConfig('alpine_printpdf_printers/printer/full'))
                ->setPaperSizeX(Mage::getStoreConfig('alpine_printpdf/qz/full_size_x'))
                ->setPaperSizeY(Mage::getStoreConfig('alpine_printpdf/qz/full_size_y'));
        }

        $this->renderLayout();
    }

    /**
     * Print action for multiple orders from Grid page
     */
    public function ordersAction()
    {
        $post   = $this->getRequest()->getPost();
        $pdfUrl = Mage::helper('adminhtml')->getUrl('adminhtml/pdfCustomiser_sales_order/pdforders');

        try {
            $this->loadLayout();

            /** @var Alpine_PrintPdf_Block_Iframe $iframe */
            $iframe = $this->getLayout()->getBlock('iframe');

            if ($iframe) {
                $iframe
                    ->setPdfUrl($pdfUrl)
                    ->setPassedParameters($post)
                    ->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_POST)
                    ->setPrinter(Mage::getStoreConfig('alpine_printpdf_printers/printer/full'))
                    ->setPaperSizeX(Mage::getStoreConfig('alpine_printpdf/qz/full_size_x'))
                    ->setPaperSizeY(Mage::getStoreConfig('alpine_printpdf/qz/full_size_y'));

                if (Mage::getStoreConfig('alpine_printpdf/qz/enabled')) {
                    $this->_pregenerateOrders($iframe);
                }
            }

            $this->renderLayout();
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('adminhtml/dashboard');
        }
    }

    /**
     * @param Alpine_PrintPdf_Block_Iframe $iframe
     * @throws Exception
     */
    protected function _pregenerateOrders(Alpine_PrintPdf_Block_Iframe $iframe)
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/order')->renderPdf(null, $orderIds, null, true);

            if ($pdf->getPdfAnyOutput()) {
                // Reset pointer to the last page
                $pdf->lastPage();
                $this->_updateIframe($iframe, $pdf->Output(null, 'S'));
            } else {
                throw new Exception(
                    Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
                );
            }
        } else {
            throw new Exception(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
        }
    }

    /**
     * Print action for single invoice (from Invoice View page)
     */
    public function invoiceAction()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');

        $pdfUrl = Mage::helper('adminhtml')
            ->getUrl('adminhtml/pdfCustomiser_sales_order/pdfinvoice', array('invoice_id' => $invoiceId));

        $this->loadLayout();

        /** @var Alpine_PrintPdf_Block_Iframe $iframe */
        $iframe = $this->getLayout()->getBlock('iframe');

        if ($iframe) {
            $iframe
                ->setPdfUrl($pdfUrl)
                ->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_GET)
                ->setPrinter(Mage::getStoreConfig('alpine_printpdf_printers/printer/full'))
                ->setPaperSizeX(Mage::getStoreConfig('alpine_printpdf/qz/full_size_x'))
                ->setPaperSizeY(Mage::getStoreConfig('alpine_printpdf/qz/full_size_y'));
        }

        $this->renderLayout();
    }

    /**
     * Print action for multiple invoices from Grid page
     */
    public function invoicesAction()
    {
        $post   = $this->getRequest()->getPost();
        $pdfUrl = Mage::helper('adminhtml')->getUrl('adminhtml/pdfCustomiser_sales_order/pdfinvoices');

        $orderIds = array();
        if (isset($post['order_ids'])) {
            if (is_array($post['order_ids'])) {
                $orderIds = $post['order_ids'];
            } else {
                $orderIds[] = $post['order_ids'];
            }
        }

        // Checking count of available invoices
        $count = Mage::getModel('sales/order_invoice')
            ->getCollection()
            ->addFieldToFilter('order_id', array('in' => $orderIds))
            ->getSize();

        if ($count > 0) {
            try {
                $this->loadLayout();

                /** @var Alpine_PrintPdf_Block_Iframe $iframe */
                $iframe = $this->getLayout()->getBlock('iframe');

                if ($iframe) {
                    $iframe
                        ->setPdfUrl($pdfUrl)
                        ->setPassedParameters($post)
                        ->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_POST)
                        ->setPrinter(Mage::getStoreConfig('alpine_printpdf_printers/printer/full'))
                        ->setPaperSizeX(Mage::getStoreConfig('alpine_printpdf/qz/full_size_x'))
                        ->setPaperSizeY(Mage::getStoreConfig('alpine_printpdf/qz/full_size_y'));

                    if (Mage::getStoreConfig('alpine_printpdf/qz/enabled')) {
                        $this->_pregenerateInvoices($iframe);
                    }
                }

                $this->renderLayout();
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('adminhtml/dashboard');
            }
        } else {
            $this->_getSession()->addError($this->__('Selected orders have no invoices.'));
            $this->_redirect('adminhtml/dashboard');
        }
    }

    /**
     * @param Alpine_PrintPdf_Block_Iframe $iframe
     * @throws Exception
     */
    protected function _pregenerateInvoices(Alpine_PrintPdf_Block_Iframe $iframe)
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, $orderIds, null, true);

            if ($pdf->getPdfAnyOutput()) {
                // Reset pointer to the last page
                $pdf->lastPage();
                $this->_updateIframe($iframe, $pdf->Output(null, 'S'));
            } else {
                throw new Exception(
                    Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
                );
            }
        } else {
            throw new Exception(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
        }
    }

    /**
     * Print action for single order (from Order View page)
     */
    public function labelAction()
    {
        $orderId    = $this->getRequest()->getParam('order_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $type       = $this->getRequest()->getParam('type');

        $pdfUrl = Mage::helper('adminhtml')
            ->getUrl(
                'printpdf/pdflabels/onepdf',
                array(
                    'order_id'    => $orderId,
                    'shipment_id' => $shipmentId,
                    'type'        => $type,
                    'custom_size' => 1
                )
            );

        $this->loadLayout();

        /** @var Alpine_PrintPdf_Block_Iframe $iframe */
        $iframe = $this->getLayout()->getBlock('iframe');

        if ($iframe) {
            $iframe
                ->setPdfUrl($pdfUrl)
                ->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_GET)
                ->setPrinter(Mage::getStoreConfig('alpine_printpdf_printers/printer/label'))
                ->setPaperSizeX(Mage::getStoreConfig('alpine_printpdf/qz/label_size_x'))
                ->setPaperSizeY(Mage::getStoreConfig('alpine_printpdf/qz/label_size_y'));
        }

        $this->renderLayout();
    }

    /**
     * Returns previously generated PDF file
     */
    public function pregeneratedAction()
    {
        $filename = $this->getRequest()->getParam('file');
        $pdf = Mage::helper('alpine_printpdf/pdf')->loadFile($filename);

        if ($pdf) {
            $this->getResponse()->setHeader('Content-Disposition', 'inline; filename=result.pdf');
            $this->getResponse()->setHeader('Content-type', 'application/pdf');
            $this->getResponse()->setBody($pdf);
        } else {
            $this->_getSession()->addError($this->__('Selected orders have no invoices.'));
            $this->_redirect('adminhtml/dashboard');
        }
    }

    /**
     * Print action for multiple packingslips from Grid page
     */
    public function packingslipsAction()
    {
        $post   = $this->getRequest()->getPost();
        $pdfUrl = Mage::helper('adminhtml')->getUrl('adminhtml/pdfCustomiser_sales_order/pdfshipments');

        try {
            $this->loadLayout();

            /** @var Alpine_PrintPdf_Block_Iframe $iframe */
            $iframe = $this->getLayout()->getBlock('iframe');

            if ($iframe) {
                $iframe
                    ->setPdfUrl($pdfUrl)
                    ->setPassedParameters($post)
                    ->setMethod(Alpine_PrintPdf_Block_Iframe::METHOD_POST)
                    ->setPrinter(Mage::getStoreConfig('alpine_printpdf_printers/printer/full'))
                    ->setPaperSizeX(Mage::getStoreConfig('alpine_printpdf/qz/full_size_x'))
                    ->setPaperSizeY(Mage::getStoreConfig('alpine_printpdf/qz/full_size_y'));

                if (Mage::getStoreConfig('alpine_printpdf/qz/enabled')) {
                    $this->_pregeneratePackingslips($iframe);
                }
            }

            $this->renderLayout();
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('adminhtml/dashboard');
        }
    }

    /**
     * @param Alpine_PrintPdf_Block_Iframe $iframe
     * @throws Exception
     */
    protected function _pregeneratePackingslips(Alpine_PrintPdf_Block_Iframe $iframe)
    {
        $orderIds    = $this->getRequest()->getPost('order_ids');
        $shipmentIds = $this->getRequest()->getPost('shipment_ids');

        if (sizeof($orderIds)) {
            if (!Fooman_PdfCustomiser_Model_Abstract::COMPAT_MODE) {
                $pdf = Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, $orderIds, null, true);
                if ($pdf->getPdfAnyOutput()) {
                    // Reset pointer to the last page
                    $pdf->lastPage();
                    $this->_updateIframe($iframe, $pdf->Output(null, 'S'));
                } else {
                    throw new Exception(
                        Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
                    );
                }
            } else {
                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(null, $orderIds)->render();
                if ($pdf) {
                    $this->_updateIframe($iframe, $pdf);
                } else {
                    throw new Exception(
                        Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
                    );
                }
            }
        } elseif (sizeof($shipmentIds)) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))
                ->load();
            $pdf = Mage::getModel('pdfcustomiser/shipment')->renderPdf($shipments, null, null, true);

            if ($pdf->getPdfAnyOutput()) {
                // Reset pointer to the last page
                $pdf->lastPage();
                $this->_updateIframe($iframe, $pdf->Output(null, 'S'));
            } else {
                throw new Exception(
                    Mage::helper('adminhtml')->__('There are no printable documents related to selected shipments')
                );
            }
        } else {
            throw new Exception(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected items')
            );
        }
    }

}