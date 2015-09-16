<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Creditmemo extends Fooman_PdfCustomiser_Model_Abstract
{
    const PDFCUSTOMISER_PDF_TYPE='creditmemo';

    /**
     * return type of pdf being rendered
     *
     * @param void
     * @access public
     *
     * @return string
     */
    public function getPdfType()
    {
        return self::PDFCUSTOMISER_PDF_TYPE;
    }

    /**
     * Creates creditmemo pdf using the tcpdf library
     * pdf is either returned as object or sent to
     * the browser
     *
     * @param array  $creditmemosGiven
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
        $creditmemosGiven = array(), $orderIds = array(), $pdf = null, $suppressOutput = false, $outputFileName = '',
        $forceStoreId = null, $hideLogo = null
    ) {

        if (empty($pdf) && empty($creditmemosGiven) && empty($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
            return false;
        }

        //we will be working through an array of orderIds later - fill it up if only creditmemos is given
        if (!empty($creditmemosGiven)) {
            foreach ($creditmemosGiven as $creditmemoGiven) {
                $currentOrderId = $creditmemoGiven->getOrder()->getId();
                $orderIds[] = $currentOrderId;
                $creditmemoIds[$currentOrderId] = $creditmemoGiven->getId();
            }
        }

        $this->_beforeGetPdf();

        // create new creditmemo helper
        /* var $creditmemoHelper Fooman_PdfCustomiser_Helper_Pdf_Creditmemo */
        $creditmemoHelper = Mage::helper('pdfcustomiser/pdf_creditmemo');

        $storeId = Mage::getModel('sales/order')->load(current($orderIds))->getStoreId();

        //work with a new pdf or add to existing one
        if (empty($pdf)) {
            $pdf = $this->getMypdfModel($storeId);
        }
        $printedIncrements = array();
        foreach ($orderIds as $orderId) {
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!empty($creditmemosGiven)) {
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->addAttributeToFilter('entity_id', $creditmemoIds[$orderId])
                    ->load();
            } else {
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->load();
            }
            if ($creditmemos->getSize() > 0) {
                foreach ($creditmemos as $creditmemo) {
                    $i = $pdf->getNumPages();
                    if ($i > 0) {
                        $pdf->endPage();
                    }

                    if (empty($creditmemosGiven)) {
                        $creditmemo->load($creditmemo->getId());
                    }

                    $storeId = $creditmemo->getStoreId();
                    if (is_null($storeId)) {
                        //if store is deleted - the store_id is removed
                        $storeId = Mage::app()->getDefaultStoreView()->getId();
                    }
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

                    $creditmemoHelper->setStoreId($storeId);
                    $creditmemoHelper->setSalesObject($creditmemo);
                    $creditmemoHelper->setPdf($pdf);
                    $pdf->setStoreId($storeId);
                    $pdf->setPdfHelper($creditmemoHelper);
                    // set standard pdf info
                    $pdf->SetStandard($creditmemoHelper);

                    // add a new page
                    $pdf->setIncrementId($creditmemo->getIncrementId());
                    $printedIncrements[]= $creditmemo->getIncrementId();

                    if ($i == 0) {
                        $pdf->AddPage();
                    } else {
                        $pdf->startPage();
                    }

                    // Print the logo
                    if ($creditmemoHelper->getPrintBarcode()) {
                        $pdf->printHeader($creditmemoHelper, $creditmemoHelper->getPdfTitle(), $order->getIncrementId(), $hideLogo);
                    } else {
                        $pdf->printHeader($creditmemoHelper, $creditmemoHelper->getPdfTitle(), false, $hideLogo);
                    }

                    // Prepare Line Items
                    $pdf->prepareLineItems($creditmemoHelper, $creditmemo, $order);

                    // Prepare Top
                    $topTemplate = $creditmemoHelper->getTemplateFileWithPath(
                        $creditmemoHelper,
                        'top',
                        self::PDFCUSTOMISER_PDF_TYPE
                    );
                    $top = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdf($pdf)
                        ->setPdfHelper($creditmemoHelper)
                        ->setTemplate($topTemplate)
                        ->toHtml();

                    $processor = Mage::helper('cms')->getBlockTemplateProcessor();
                    $processor->setVariables(
                        array(
                            'order'           => $order,
                            'sales_object'    => $creditmemo,
                            'billing_address' => $pdf->PrepareCustomerAddress($creditmemoHelper, $order, 'billing'),
                            'shipping_address'=> $pdf->PrepareCustomerAddress($creditmemoHelper, $order, 'shipping'),
                            'payment'         => $pdf->PreparePayment($creditmemoHelper, $order, $creditmemo),
                            'shipping'        => nl2br($pdf->PrepareShipping($creditmemoHelper, $order, $creditmemo))
                        )
                    );
                    $top = $processor->filter($top);

                    //Prepare Totals
                    $totals = $this->PrepareTotals($creditmemoHelper, $creditmemo);

                    //Prepare Bottom
                    $bottomTemplate = $creditmemoHelper->getTemplateFileWithPath(
                        $creditmemoHelper,
                        'bottom',
                        self::PDFCUSTOMISER_PDF_TYPE
                    );
                    $bottom = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                            ->setPdf($pdf)
                            ->setPdfHelper($creditmemoHelper)
                            ->setTotals($totals)
                            ->setTemplate($bottomTemplate)
                            ->toHtml();
                    $processor->setVariables(
                        array(
                            'order'        => $order,
                            'sales_object' => $creditmemo
                        )
                    );
                    $bottom = $processor->filter($bottom);

                    //Prepare Items
                    $itemsTemplate = $creditmemoHelper->getTemplateFileWithPath(
                        $creditmemoHelper,
                        'items'
                    );
                    $items = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_items')
                        ->setPdf($pdf)
                        ->setPdfHelper($creditmemoHelper)
                        ->setTemplate($itemsTemplate)
                        ->toHtml();

                    //Put it all together
                    $pdf->writeHTML($top, false);
                    $pdf->SetFont($creditmemoHelper->getPdfFont(), '', $creditmemoHelper->getPdfFontsize('small'));
                    $pdf->writeHTML($items, false, false, false, false, '');
                    $pdf->SetFont($creditmemoHelper->getPdfFont(), '', $creditmemoHelper->getPdfFontsize());
                    //reset Margins in case there was a page break
                    $pdf->setMargins(
                        $creditmemoHelper->getPdfMargins('sides'), $creditmemoHelper->getPdfMargins('top')
                    );
                    $pdf->writeHTML($bottom, false);

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
                    $creditmemoHelper->getPdfFileName($printedIncrements, '.pdf', $outputFileName),
                    $creditmemoHelper->getNewWindow()
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
