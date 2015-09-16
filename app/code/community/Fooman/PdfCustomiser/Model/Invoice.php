<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Invoice extends Fooman_PdfCustomiser_Model_Abstract
{
    const PDFCUSTOMISER_PDF_TYPE='invoice';

    /**
     * return type of pdf being rendered
     *
     * @return string
     */
    public function getPdfType()
    {
        return self::PDFCUSTOMISER_PDF_TYPE;
    }

    /**
     * Creates invoice pdf using the tcpdf library
     * pdf is either returned as object or sent to
     * the browser
     *
     * @param array  $invoicesGiven
     * @param array  $orderIds
     * @param null   $pdf
     * @param bool   $suppressOutput
     * @param string $outputFileName
     * @param null   $forceStoreId
     *
     * @access public
     *
     * @return bool|Fooman_PdfCustomiser_Model_Mypdf
     */
    public function renderPdf(
        $invoicesGiven = array(), $orderIds = array(), $pdf = null, $suppressOutput = false, $outputFileName = '',
        $forceStoreId = null,  $hideLogo = null
    ) {

        if (empty($pdf) && empty($invoicesGiven) && empty($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
            return false;
        }

        //we will be working through an array of orderIds later - fill it up if only invoices are given
        if (!empty($invoicesGiven)) {
            foreach ($invoicesGiven as $invoiceGiven) {
                $currentOrderId = $invoiceGiven->getOrder()->getId();
                $orderIds[$invoiceGiven->getId()] = $currentOrderId;
            }
        }

        $this->_beforeGetPdf();

        //need to get the store id from the first order to initialise pdf
        $storeId = Mage::getModel('sales/order')->load(current($orderIds))->getStoreId();

        //work with a new pdf or add to existing one
        if (empty($pdf)) {
            $pdf = $this->getMypdfModel($storeId);
        }

        $printedIncrements = array();
        foreach ($orderIds as $key => $orderId) {
            //load data

            $order = Mage::getModel('sales/order')->load($orderId);
            if (!empty($invoicesGiven)) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->addAttributeToFilter('entity_id', $key)
                    ->load();
            } else {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->load();
            }

            //loop over invoices
            if ($invoices->getSize() > 0) {
                foreach ($invoices as $invoice) {
                    $i = $pdf->getNumPages();
                    if ($i > 0) {
                        $pdf->endPage();
                    }

                    // create new invoice helper
                    /* var $invoiceHelper Fooman_PdfCustomiser_Helper_Pdf_Invoice */
                    $invoiceHelper = Mage::helper('pdfcustomiser/pdf_invoice');
                    if (empty($invoicesGiven)) {
                        $invoice->load($invoice->getId());
                    }
                    $storeId = $invoice->getStoreId();
                    //force to print from an alternative store
                    if (isset($forceStoreId)) {
                        $storeId = $forceStoreId;
                    }
                    if ($storeId) {
                        $appEmulation = Mage::getSingleton('core/app_emulation');
                        $initial = $appEmulation->startEnvironmentEmulation(
                            $storeId, Mage_Core_Model_App_Area::AREA_FRONTEND, true
                        );
                    }

                    $invoiceHelper->setStoreId($storeId);
                    $invoiceHelper->setSalesObject($invoice);
                    $invoiceHelper->setPdf($pdf);
                    $pdf->setStoreId($storeId);
                    $pdf->setPdfHelper($invoiceHelper);
                    // set standard pdf info
                    $pdf->SetStandard($invoiceHelper);

                    // add a new page
                    $pdf->setIncrementId($invoice->getIncrementId());
                    $printedIncrements[]= $invoice->getIncrementId();
                    if ($i == 0) {
                        $pdf->AddPage();
                    } else {
                        $pdf->startPage();
                    }

                    // Print the logo
                    if ($invoiceHelper->getPrintBarcode()) {
                        $pdf->printHeader($invoiceHelper, $invoiceHelper->getPdfTitle(), $order->getIncrementId(), $hideLogo);
                    } else {
                        $pdf->printHeader($invoiceHelper, $invoiceHelper->getPdfTitle(), false, $hideLogo);
                    }

                    // Prepare Line Items
                    $pdf->prepareLineItems($invoiceHelper, $invoice, $order);

                    // Prepare Top
                    $topTemplate = $invoiceHelper->getTemplateFileWithPath(
                        $invoiceHelper,
                        'top',
                        self::PDFCUSTOMISER_PDF_TYPE
                    );
                    $top = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdf($pdf)
                        ->setPdfHelper($invoiceHelper)
                        ->setTemplate($topTemplate)
                        ->toHtml();

                    $processor = Mage::helper('cms')->getBlockTemplateProcessor();
                    $processor->setVariables(
                        array(
                            'order' => $order,
                            'sales_object' => $invoice,
                            'billing_address'=> $pdf->PrepareCustomerAddress($invoiceHelper, $order, 'billing'),
                            'shipping_address'=> $pdf->PrepareCustomerAddress($invoiceHelper, $order, 'shipping'),
                            'payment'=> $pdf->PreparePayment($invoiceHelper, $order, $invoice),
                            'shipping'=> nl2br($pdf->PrepareShipping($invoiceHelper, $order, $invoice))
                        )
                    );
                    $top = $processor->filter($top);

                    //Prepare Totals
                    $totals = $this->PrepareTotals($invoiceHelper, $invoice);

                    //Prepare Bottom
                    $bottomTemplate = $invoiceHelper->getTemplateFileWithPath(
                        $invoiceHelper,
                        'bottom',
                        self::PDFCUSTOMISER_PDF_TYPE
                    );
                    $bottom = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdf($pdf)
                        ->setPdfHelper($invoiceHelper)
                        ->setTotals($totals)
                        ->setTemplate($bottomTemplate)
                        ->toHtml();
                    $processor->setVariables(
                        array(
                            'order'        => $order,
                            'sales_object' => $invoice
                        )
                    );
                    $bottom = $processor->filter($bottom);

                    //Prepare Items
                    $itemsTemplate = $invoiceHelper->getTemplateFileWithPath(
                        $invoiceHelper,
                        'items'
                    );
                    $items = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_items')
                        ->setPdf($pdf)
                        ->setPdfHelper($invoiceHelper)
                        ->setTemplate($itemsTemplate)
                        ->toHtml();

                    //Put it all together
                    $pdf->writeHTML($top, false);
                    $pdf->SetFont($invoiceHelper->getPdfFont(), '', $invoiceHelper->getPdfFontsize('small'));
                    $pdf->writeHTML($items, false, false, false, false, '');
                    $pdf->SetFont($invoiceHelper->getPdfFont(), '', $invoiceHelper->getPdfFontsize());
                    //reset Margins in case there was a page break
                    $pdf->setMargins($invoiceHelper->getPdfMargins('sides'), $invoiceHelper->getPdfMargins('top'));
                    $pdf->writeHTML($bottom, false);

                    /*
                    //Uncomment this block: delete /* and * /
                    //to add legal text for German invoices. EuVat Extension erforderlich
                    switch($order->getCustomerGroupId()){
                        case 2:
                            $pdf->Cell(0, 0, 'steuerfrei nach § 4 Nr. 1 b UStG', 0, 2, 'L',null,null,1);
                            break;
                        case 1:
                            $pdf->Cell(0, 0, 'umsatzsteuerfreie Ausfuhrlieferung', 0, 2, 'L',null,null,1);
                            break;
                    }
                     */

                    //print extra addresses for peel off labels
                    if ($invoiceHelper->getPdfIntegratedLabels()) {
                        $pdf->OutputCustomerAddresses($invoiceHelper, $order, $invoiceHelper->getPdfIntegratedLabels());
                    }
                    $pdf->endPage();
                    if ($storeId) {
                        $appEmulation->stopEnvironmentEmulation($initial);
                    }
                    $pdf->setPdfAnyOutput(true);
                }
            }
        }

        //output PDF document
        if (!$suppressOutput) {
            if ($pdf->getPdfAnyOutput()) {
                // reset pointer to the last page
                $pdf->lastPage();
                $pdf->Output(
                    $invoiceHelper->getPdfFileName($printedIncrements),
                    $invoiceHelper->getNewWindow()
                );
                exit;
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
                );
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

}
