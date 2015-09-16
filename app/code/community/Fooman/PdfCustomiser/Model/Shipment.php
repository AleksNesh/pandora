<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Shipment extends Fooman_PdfCustomiser_Model_Abstract
{
    const PDFCUSTOMISER_PDF_TYPE='shipment';

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
     * Creates packinslip pdf using the tcpdf library
     * pdf is either returned as object or sent to
     * the browser
     *
     * @param array  $shipmentsGiven
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
        $shipmentsGiven = array(), $orderIds = array(), $pdf = null, $suppressOutput = false, $outputFileName = '',
        $forceStoreId = null, $hideLogo = null
    ) {

        if (empty($pdf) && empty($shipmentsGiven) && empty($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
            return false;
        }

        //we will be working through an array of orderIds later - fill it up if only shipments are given
        if (!empty($shipmentsGiven)) {
            $allShipments = array();
            foreach ($shipmentsGiven as $shipmentGiven) {
                $currentOrderId = $shipmentGiven->getOrder()->getId();
                $orderIds[$currentOrderId] = $currentOrderId;
                $allShipments[$currentOrderId][] = $shipmentGiven;
            }
        }

        $this->_beforeGetPdf();

        $storeId = Mage::getModel('sales/order')->load(current($orderIds))->getStoreId();

        //work with a new pdf or add to existing one
        if (empty($pdf)) {
            $pdf = $this->getMypdfModel($storeId);
        }

        $printedIncrements = array();
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $printOrderAsPackingSlip =  Mage::getStoreConfig('sales_pdf/shipment/shipmentuseorder', $storeId);
            if (!empty($shipmentsGiven)) {
                $shipments = $allShipments[$orderId];
            } elseif ($printOrderAsPackingSlip) {
                $shipments = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToFilter('entity_id', $orderId)
                    ->load();
                //$shipments = array($order->prepareShipment());
            } else {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->load();
            }

            if (count($shipments) > 0 || $shipments->getSize() > 0) {
                foreach ($shipments as $shipment) {
                    $i = $pdf->getNumPages();
                    if ($i > 0) {
                        $pdf->endPage();
                    }

                    // create new Shipment helper
                    /* var $shipmentHelper Fooman_PdfCustomiser_Helper_Pdf_Shipment */
                    $shipmentHelper = Mage::helper('pdfcustomiser/pdf_shipment');
                    if (empty($shipmentsGiven)) {
                        $shipment->load($shipment->getId());
                    }

                    $storeId = $shipment->getStoreId();
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

                    $shipmentHelper->setStoreId($storeId);
                    $shipmentHelper->setSalesObject($shipment);
                    $shipmentHelper->setPdf($pdf);
                    $pdf->setStoreId($storeId);
                    $pdf->setPdfHelper($shipmentHelper);
                    // set standard pdf info
                    $pdf->SetStandard($shipmentHelper);

                    // add a new page
                    $pdf->setIncrementId($shipment->getIncrementId());
                    $printedIncrements[]= $shipment->getIncrementId();
                    if ($i == 0) {
                        $pdf->AddPage();
                    } else {
                        $pdf->startPage();
                    }

                    // Print the logo
                    if ($shipmentHelper->getPrintBarcode()) {
                        $pdf->printHeader($shipmentHelper, $shipmentHelper->getPdfTitle(), $order->getIncrementId(), $hideLogo);
                    } else {
                        $pdf->printHeader($shipmentHelper, $shipmentHelper->getPdfTitle(), false, $hideLogo);
                    }

                    // Prepare Line Items
                    $pdf->prepareLineItems($shipmentHelper, $shipment, $order);

                    // Prepare Top
                    $topTemplate = $shipmentHelper->getTemplateFileWithPath(
                        $shipmentHelper,
                        'top',
                        self::PDFCUSTOMISER_PDF_TYPE
                    );
                    $top = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdf($pdf)
                        ->setPdfHelper($shipmentHelper)
                        ->setTemplate($topTemplate)
                        ->toHtml();

                    $processor = Mage::helper('cms')->getBlockTemplateProcessor();
                    $processor->setVariables(
                        array(
                            'order' => $order,
                            'sales_object' => $shipment,
                            'billing_address'=> $pdf->PrepareCustomerAddress($shipmentHelper, $order, 'billing'),
                            'shipping_address'=> $pdf->PrepareCustomerAddress($shipmentHelper, $order, 'shipping'),
                            'payment'=> $pdf->PreparePayment($shipmentHelper, $order, $shipment),
                            'shipping'=> nl2br($pdf->PrepareShipping($shipmentHelper, $order, $shipment))
                        )
                    );
                    $top = $processor->filter($top);

                    //Prepare Totals
                    $totals = $this->PrepareTotals($shipmentHelper, $shipment);

                    //Prepare Bottom
                    $bottomTemplate = $shipmentHelper->getTemplateFileWithPath(
                        $shipmentHelper,
                        'bottom',
                        self::PDFCUSTOMISER_PDF_TYPE
                    );
                    $bottom = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdf($pdf)
                        ->setPdfHelper($shipmentHelper)
                        ->setTotals($totals)
                        ->setTemplate($bottomTemplate)
                        ->toHtml();
                    $processor->setVariables(
                        array(
                            'order'        => $order,
                            'sales_object' => $shipment
                        )
                    );
                    $bottom = $processor->filter($bottom);

                    //Prepare Items
                    $itemsTemplate = $shipmentHelper->getTemplateFileWithPath(
                        $shipmentHelper,
                        'items'
                    );
                    $items = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_items')
                        ->setPdf($pdf)
                        ->setPdfHelper($shipmentHelper)
                        ->setTemplate($itemsTemplate)
                        ->toHtml();

                    //Put it all together
                    $pdf->writeHTML($top, false);
                    $pdf->SetFont($shipmentHelper->getPdfFont(), '', $shipmentHelper->getPdfFontsize('small'));
                    $pdf->writeHTML($items, false, false, false, false, '');
                    $pdf->SetFont($shipmentHelper->getPdfFont(), '', $shipmentHelper->getPdfFontsize());
                    //reset Margins in case there was a page break
                    $pdf->setMargins($shipmentHelper->getPdfMargins('sides'), $shipmentHelper->getPdfMargins('top'));
                    $pdf->writeHTML($bottom, false);

                    //print extra addresses for peel off labels
                    if ($shipmentHelper->getPdfIntegratedLabels()) {
                        $pdf->OutputCustomerAddresses(
                            $shipmentHelper, $order, $shipmentHelper->getPdfIntegratedLabels()
                        );
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
                    $shipmentHelper->getPdfFileName($printedIncrements),
                    $shipmentHelper->getNewWindow()
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
