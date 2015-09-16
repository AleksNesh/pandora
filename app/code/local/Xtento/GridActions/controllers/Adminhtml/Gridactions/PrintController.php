<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2013-09-04T14:30:24+02:00
 * File:          app/code/local/Xtento/GridActions/controllers/Adminhtml/Gridactions/PrintController.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Adminhtml_GridActions_PrintController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Print invoices for selected orders
     */
    public function pdfinvoicesAction()
    {
        $orderIds = explode(",", $this->getRequest()->getParam('order_ids'));
        $flag = false;
        if (!empty($orderIds)) {
            //foreach ($orderIds as $orderId) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setOrderFilter($orderIds) // Be careful: Could be because of PdfCustomizer extension. Should be $orderId - why does the PDF get returned instantly?
                ->load();
            if ($invoices->getSize() > 0) {
                $flag = true;
                if (!isset($pdf)) {
                    $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                } else {
                    $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    $pdf->pages = array_merge($pdf->pages, $pages->pages);
                }
            }
            //}
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoices_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('adminhtml/sales_order');
            }
        }
        $this->_redirect('adminhtml/sales_order');
    }

    /**
     * Print shipments for selected orders
     */
    public function pdfshipmentsAction()
    {
        $orderIds = explode(",", $this->getRequest()->getParam('order_ids'));
        $flag = false;
        if (!empty($orderIds)) {
            //foreach ($orderIds as $orderId) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderIds) // Be careful: Could be because of PdfCustomizer extension. Should be $orderId - why does the PDF get returned instantly?
                ->load();
            if ($shipments->getSize() > 0) {
                $flag = true;
                if (!isset($pdf)) {
                    $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                } else {
                    $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    $pdf->pages = array_merge($pdf->pages, $pages->pages);
                }
            }
            //}
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'packingslips_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('adminhtml/sales_order');
            }
        }
        $this->_redirect('adminhtml/sales_order');
    }

    /**
     * Print shipping labels for selected orders, only for supported carriers, just like the "Print shipping labels" mass action
     */
    public function pdflabelsAction()
    {
        $orderIds = explode(",", $this->getRequest()->getParam('order_ids'));
        if (!empty($orderIds)) {
            //foreach ($orderIds as $orderId) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderIds) // Be careful: Could be because of PdfCustomizer extension. Should be $orderId - why does the PDF get returned instantly?
                ->load();

            if ($shipments && $shipments->getSize()) {
                foreach ($shipments as $shipment) {
                    $labelContent = $shipment->getShippingLabel();
                    if ($labelContent) {
                        $labelsContent[] = $labelContent;
                    }
                }
            }
            if (!empty($labelsContent)) {
                $outputPdf = $this->_combineLabelsPdf($labelsContent);
                $this->_prepareDownloadResponse('ShippingLabels.pdf', $outputPdf->render(), 'application/pdf');
                return;
            } else {
                $this->_getSession()->addError(Mage::helper('sales')->__('There are no shipping labels related to selected order.'));
            }
        }
        $this->_redirect('adminhtml/sales_order');
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->_createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }

    /**
     * Create Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @param string $imageString
     * @return Zend_Pdf_Page|bool
     */
    protected function _createPdfPageFromImageString($imageString)
    {
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'shipping_labels_'
            . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);
        return $page;
    }

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('sales/order/gridactions/actions');
	}
}