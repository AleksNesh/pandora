<?php
/**
 * Order Pdf Render
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2015 Alpine Consulting, Inc
 * @author      dmitry.soloma@alpineinc.com
 */
 
 /**
 * class Alpine_PrintPdf_Model_Order
 *
 * @category   Alpine
 * @package    Alpine_PrintPdf
 */ 
class Alpine_PrintPdf_Model_PdfCustomiser_Order extends Fooman_PdfCustomiser_Model_Order
{
    /**
     * Creates order pdf using the tcpdf library
     * pdf is either returned as object or sent to
     * the browser
     *
     * @param array  $ordersGiven
     * @param array  $orderIds
     * @param null   $pdf
     * @param bool   $suppressOutput
     * @param string $outputFileName
     * @param null   $forceStoreId
     *
     * @return bool|Fooman_PdfCustomiser_Model_Mypdf
     */
    public function renderPdf(
        $ordersGiven = array(), $orderIds = array(), $pdf = null, $suppressOutput = false, $outputFileName = '',
        $forceStoreId = null, $hideLogo = null
    ) {

        //check if there is anything to print
        if (empty($pdf) && empty($ordersGiven) && empty($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
            return false;
        }

        //we will be working through an array of orderIds later - fill it up if only $ordersGiven is available
        if (!empty($ordersGiven)) {
            foreach ($ordersGiven as $orderGiven) {
                $orderIds[] = $orderGiven->getId();
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
            $i = $pdf->getNumPages();
            if ($i > 0) {
                $pdf->endPage();
            }
            //load data
            $order = Mage::getModel('sales/order')->load($orderId);

            // create new helper
            /* var $orderHelper Fooman_PdfCustomiser_Helper_Pdf_Order */
            $orderHelper = Mage::helper('pdfcustomiser/pdf_order');

            $storeId = $order->getStoreId();
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

            $orderHelper->setStoreId($storeId);
            $orderHelper->setSalesObject($order);
            $orderHelper->setPdf($pdf);
            $pdf->setStoreId($storeId);
            $pdf->setPdfHelper($orderHelper);
            // set standard pdf info
            $pdf->SetStandard($orderHelper);

            // add a new page
            $pdf->setIncrementId($order->getIncrementId());
            $printedIncrements[]= $order->getIncrementId();
            if ($i == 0) {
                $pdf->AddPage();
            } else {
                $pdf->startPage();
            }

            // Print the logo
            if ($orderHelper->getPrintBarcode()) {
                $pdf->printHeader($orderHelper, $orderHelper->getPdfTitle(), $order->getIncrementId(), $hideLogo);
            } else {
                $pdf->printHeader($orderHelper, $orderHelper->getPdfTitle(), false, $hideLogo);
            }

            // Prepare Line Items
            $pdf->prepareLineItems($orderHelper, $order, $order);

            // Prepare Top
            $topTemplate = $orderHelper->getTemplateFileWithPath(
                $orderHelper,
                'top',
                self::PDFCUSTOMISER_PDF_TYPE
            );
            $top = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                ->setPdf($pdf)
                ->setPdfHelper($orderHelper)
                ->setTemplate($topTemplate)
                ->toHtml();

            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            $processor->setVariables(
                array(
                    'order'           => $order,
                    'sales_object'    => $order,
                    'billing_address' => $pdf->PrepareCustomerAddress($orderHelper, $order, 'billing'),
                    'shipping_address'=> $pdf->PrepareCustomerAddress($orderHelper, $order, 'shipping'),
                    'payment'         => $pdf->PreparePayment($orderHelper, $order, $order),
                    'shipping'        => nl2br($pdf->PrepareShipping($orderHelper, $order, $order))
                )
            );
            $top = $processor->filter($top);

            //Prepare Totals
            $totals = $this->PrepareTotals($orderHelper, $order);

            //Prepare Bottom
            $bottomTemplate = $orderHelper->getTemplateFileWithPath(
                $orderHelper,
                'bottom',
                self::PDFCUSTOMISER_PDF_TYPE
            );
            $bottom = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                ->setPdf($pdf)
                ->setPdfHelper($orderHelper)
                ->setTotals($totals)
                ->setTemplate($bottomTemplate)
                ->toHtml();
            $processor->setVariables(
                array(
                    'order' => $order,
                    'sales_object' => $order
                )
            );
            $bottom = $processor->filter($bottom);

            //Prepare Items
            $itemsTemplate = $orderHelper->getTemplateFileWithPath(
                $orderHelper,
                'items'
            );
            $items = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_items')
                ->setPdf($pdf)->setPdfHelper($orderHelper)
                ->setTemplate($itemsTemplate)
                ->toHtml();

            $showGC = self::showGiftWrapCardInfo($order->getStoreId());
            $showGWM = self::showGiftWrapMessage($order->getStoreId());
            $giftWrapTemplate = 'gift-wrap-items.phtml';

            $giftBoxCollection = self::getGiftBoxCollection($order);
            $giftItems = Mage::app()->getLayout()->createBlock('giftwrap/giftbox')
                ->setPdf($pdf)->setPdfHelper($orderHelper)
                ->setGiftBoxData($giftBoxCollection)
                ->setTemplate('fooman/pdfcustomiser/' . $giftWrapTemplate)
                ->setShowGC($showGC)
                ->setShowGWM($showGWM)
                ->setOrder($order)
                ->toHtml();

            //Put it all together
            $pdf->writeHTML($top, false);
            $pdf->SetFont($orderHelper->getPdfFont(), '', $orderHelper->getPdfFontsize('small'));
            $pdf->writeHTML($items, false, false, false, false, '');
            $pdf->writeHTML($giftItems, false, false, false, false, '');
            $pdf->SetFont($orderHelper->getPdfFont(), '', $orderHelper->getPdfFontsize());
            //reset Margins in case there was a page break
            $pdf->setMargins($orderHelper->getPdfMargins('sides'), $orderHelper->getPdfMargins('top'));
            $pdf->writeHTML($bottom, false);

            $pdf->endPage();

            if ($storeId) {
                $appEmulation->stopEnvironmentEmulation($initial);
            }
            $pdf->setPdfAnyOutput(true);
        }

        //output PDF document
        if (!$suppressOutput) {
            if ($pdf->getPdfAnyOutput()) {
                // reset pointer to the last page
                $pdf->lastPage();
                $pdf->Output(
                    $orderHelper->getPdfFileName($printedIncrements),
                    $orderHelper->getNewWindow()
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


    /**
     * Get gift box collection
     *
     * @param Mage_Sales_Model_Order |
     *  Mage_Sales_Model_Order_Invoice |
     *  Mage_Sales_Model_Order_shipment $entity
     * @return array
     */
    public static function getGiftBoxCollection($entity)
    {
        $quoteId = null;
        $giftBoxCollection = array();

        if ($entity instanceof Mage_Sales_Model_Order) {
            $quoteId = $entity->getQuoteId();
            $entityItemIds = Mage::getResourceModel('sales/order_item_collection')
                ->setOrderFilter($entity)
                ->getColumnValues('quote_item_id');
        } else {
            $orderId = $entity->getOrderId();
            $order = Mage::getModel('sales/order')->load($orderId);
            $quoteId = $order->getQuoteId();
            $entityOrderItemIds = $entity->getItemsCollection()
                ->getColumnValues('order_item_id');
            $entityItemIds = Mage::getResourceModel('sales/order_item_collection')
                ->setOrderFilter($order)
                ->addFieldToFilter('item_id', array('in' => $entityOrderItemIds))
                ->getColumnValues('quote_item_id');
        }

        if ($quoteId) {
            $giftBoxCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
        }

        if (!empty($giftBoxCollection)) {
            foreach ($giftBoxCollection as $key => $giftBox) {
                $items = Mage::getModel('giftwrap/selectionitem')
                    ->getCollection()
                    ->addFieldToFilter('selection_id', $giftBox['id'])
                    ->getColumnValues('item_id');

                $unsetGiftBox = true;
                foreach ($items as $itemId) {
                    if (in_array($itemId, $entityItemIds)) {
                        $unsetGiftBox = false;
                    }
                }
                if ($unsetGiftBox === true) {
                    unset($giftBoxCollection[$key]);
                }
            }
        }

        return $giftBoxCollection;
    }

    /**
     * Show gift wrap card info
     *
     * @param int $storeId
     * @return boolean
     */
    public static function showGiftWrapCardInfo($storeId)
    {
        return (boolean)Mage::getStoreConfig('sales_pdf/order/display_card', $storeId);
    }

    /**
     * Show gift wrap card info
     *
     * @param int $storeId
     * @return boolean
     */
    public static function showGiftWrapMessage($storeId)
    {
        return (boolean)Mage::getStoreConfig('sales_pdf/order/display_gift_message', $storeId);
    }
}
