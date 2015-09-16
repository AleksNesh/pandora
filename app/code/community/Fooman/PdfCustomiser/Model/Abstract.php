<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Fooman_PdfCustomiser_Model_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * set to true to try to merge pdfs created by PdfCustomiser with other pdfs based on Zend_Pdf
     */
    const COMPAT_MODE = false;

    public $pages = array();

    /**
     * keep compatible with the Zend_Pdf way of doing things
     * collect input for later processing with render()
     *
     * @param array $input    array of items to print
     * @param array $orderIds array of order ids
     *
     * @return Fooman_PdfCustomiser_Model_Abstract | Zend_Pdf
     */
    public function getPdf($input = array(), $orderIds = null)
    {
        if (self::COMPAT_MODE) {
            try {
                $newPdf = new Zend_Pdf();
                $extractor = new Zend_Pdf_Resource_Extractor();

                if (!empty($orderIds)) {
                    $origPdf = $this->renderPdf(null, $orderIds, null, true);
                } else {
                    $origPdf = $this->renderPdf($input, $orderIds, null, true);
                }
                if ($origPdf->getPdfAnyOutput()) {
                    $pdfString = $origPdf->Output('output.pdf', 'S');

                    $tcpdf = Zend_Pdf::parse($pdfString);

                    foreach ($tcpdf->pages as $p) {
                        $newPdf->pages[] = $extractor->clonePage($p);
                    }
                }
                return $newPdf;

            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            $this->pages[] = array(
                'instance'    => $this,
                'objectArray' => $input,
                'orderIds'    => $orderIds
            );
            return $this;
        }
    }

    /**
     * get tcpdf model for pdf with overall settings applied
     *
     * @param $storeId
     *
     * @return Fooman_PdfCustomiser_Model_Mypdf
     */
    public function getMypdfModel($storeId)
    {
        return Mage::getModel(
            'pdfcustomiser/mypdf',
            array(
                 Mage::getStoreConfig('sales_pdf/all/allpageorientation', $storeId),
                 'mm',
                 Mage::getStoreConfig('sales_pdf/all/allpagesize', $storeId),
                 true,
                 'UTF-8',
                 false,
                 false
            )
        );
    }
    /**
     *
     *
     * @param bool $newSegmentOnly
     * @param null $outputStream
     * @param bool $toFileName
     * @param bool $toBrowser
     *
     * @return bool | string | void
     */
    public function render($newSegmentOnly = false, $outputStream = null, $toFileName = false, $toBrowser = false)
    {
        if (self::COMPAT_MODE) {
            return parent::render($newSegmentOnly, $outputStream);
        } else {
            $pdf = null;
            foreach ($this->pages as $printObjects) {
                if (!empty($printObjects['orderIds'])) {
                    $pdf = $printObjects['instance']->renderPdf(array(), $printObjects['orderIds'], $pdf, true);
                } else {
                    $pdf = $printObjects['instance']->renderPdf($printObjects['objectArray'], null, $pdf, true);
                }
            }

            if ($pdf->getPdfAnyOutput()) {
                if ($toFileName) {
                    if ($toBrowser) {
                        return $pdf->Output($toFileName, Mage::getStoreConfigFlag('sales_pdf/all/allnewwindow') ? 'D' : 'I');
                    } else {
                        return $pdf->Output($toFileName, 'F');
                    }
                } else {
                    return $pdf->Output('output.pdf', 'S');
                }
            } else {
                return false;
            }
        }
    }

    /**
     * retrieve order associated with sales object
     *
     * @param $salesObject
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder ($salesObject)
    {
        if ($salesObject instanceof Mage_Sales_Model_Order) {
            return $salesObject;
        } else {
            return $salesObject->getOrder();
        }
    }

    /**
     * adjust sort order of global pdf config nodes
     * based on settings in database
     *
     * @param $helper
     *
     */
    protected function adjustPdfTotalsConfig(Fooman_PdfCustomiser_Helper_Pdf $helper)
    {
        if (!$helper->hasAdjustedTotalsSort) {
            $existingConfig = Mage::getConfig()->getNode('global/pdf/totals');
            foreach ($existingConfig as $totals) {
                foreach ($totals as $total=> $settings) {
                    $newSortOrder = Mage::getStoreConfig('sales_pdf/totals/' . (string)$total, $helper->getStoreId());
                    if ($newSortOrder) {
                        Mage::getConfig()->setNode('global/pdf/totals/' . $total . '/sort_order', $newSortOrder, true);
                    }
                }
            }
        }
        $helper->hasAdjustedTotalsSort = true;
    }

    /**
     * prepare totals for display
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $salesObject
     *
     * @return array
     */
    public function PrepareTotals(Fooman_PdfCustomiser_Helper_Pdf $helper, $salesObject)
    {
        $totals = array();
        if (!$helper->displayTotals()) {
            return $totals;
        }

        $order = $this->getOrder($salesObject);
        $this->adjustPdfTotalsConfig($helper);
        $pdfTotals = $this->_getTotalsList($salesObject);

        foreach ($pdfTotals as $pdfTotal) {
            $pdfTotal->setOrder($order)->setSource($salesObject);
            $sortOrder = $pdfTotal->getSortOrder();
            switch ($pdfTotal->getSourceField()){
                case 'subtotal':
                    //Prepare Subtotal
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if (Mage::getStoreConfig('tax/sales_display/subtotal', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            if ($this->_hiddenTaxAmount == 0 && $salesObject->getBaseSubtotalInclTax()) {
                                $totals[$sortOrder][] = array(
                                    'code'      => $pdfTotal->getSourceField(),
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                    'amount'    => $salesObject->getSubtotalInclTax(),
                                    'baseAmount'=> $salesObject->getBaseSubtotalInclTax()
                                );
                            } else {
                                $totals[$sortOrder][] = array(
                                    'code'      => $pdfTotal->getSourceField(),
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                    'amount'    => $salesObject->getSubtotal()
                                        + $salesObject->getTaxAmount()
                                        + $this->_hiddenTaxAmount
                                        - $salesObject->getFoomanSurchargeTaxAmount()
                                        - $salesObject->getShippingTaxAmount()
                                        - $salesObject->getCodTaxAmount(),
                                    'baseAmount'=> $salesObject->getBaseSubtotal()
                                        + $salesObject->getBaseTaxAmount()
                                        + $this->_baseHiddenTaxAmount
                                        - $salesObject->getBaseFoomanSurchargeTaxAmount()
                                        - $salesObject->getBaseShippingTaxAmount()
                                        - $salesObject->getBaseCodTaxAmount()
                                );
                            }
                        } elseif (Mage::getStoreConfig('tax/sales_display/subtotal', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            if ($this->_hiddenTaxAmount == 0 && $salesObject->getBaseSubtotalInclTax()) {
                                $totals[$sortOrder][] = array(
                                    'code'      => $pdfTotal->getSourceField(),
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                        . Mage::helper('tax')->__('Incl. Tax') . ':',
                                    'amount'    => $salesObject->getSubtotalInclTax(),
                                    'baseAmount'=> $salesObject->getBaseSubtotalInclTax()
                                );
                            } else {
                                $totals[$sortOrder][] = array(
                                    'code'      => $pdfTotal->getSourceField(),
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                        . Mage::helper('tax')->__('Incl. Tax') . ':',
                                    'amount'    => $salesObject->getSubtotal()
                                        + $salesObject->getTaxAmount()
                                        + $this->_hiddenTaxAmount
                                        - $salesObject->getFoomanSurchargeTaxAmount()
                                        - $salesObject->getShippingTaxAmount()
                                        - $salesObject->getCodTaxAmount(),
                                    'baseAmount'=> $salesObject->getBaseSubtotal()
                                        + $salesObject->getBaseTaxAmount()
                                        + $this->_baseHiddenTaxAmount
                                        - $salesObject->getBaseFoomanSurchargeTaxAmount()
                                        - $salesObject->getBaseShippingTaxAmount()
                                        - $salesObject->getBaseCodTaxAmount()
                                );
                            }
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                    . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $salesObject->getSubtotal(),
                                'baseAmount'=> $salesObject->getBaseSubtotal()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                'amount'    => $salesObject->getSubtotal(),
                                'baseAmount'=> $salesObject->getBaseSubtotal()
                            );
                        }
                    }
                    break;
                case 'discount_amount':
                    //Prepare Discount
                    //Prepare positive or negative Discount to display with minus sign
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getDiscountAmount()>0)?-1:1;
                        if ($salesObject->getDiscountDescription()) {
                            $label = trim(Mage::helper('sales')->__('Discount').' ('. $salesObject->getDiscountDescription()). '):';
                        } else {
                            $label = trim(Mage::helper('sales')->__('Discount') . ' ' . $order->getCouponCode()) . ':';
                        }
                        if ($helper->displaySalesruleTitle()) {
                            if ($order->getCouponCode()) {
                                $salesruleTitles = array();
                                foreach (explode(',', $order->getCouponCode()) as $couponCode) {
                                    $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                                    if ($coupon) {
                                        $salesrule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
                                        if ($salesrule->getStoreLabel($helper->getStoreId())) {
                                            $salesruleTitles[] = $salesrule->getStoreLabel($helper->getStoreId());
                                        } elseif ($salesrule->getName()) {
                                            $salesruleTitles[] = $salesrule->getName();
                                        }
                                    }
                                }
                                if (!empty($salesruleTitles)) {
                                    $label = implode(' ', $salesruleTitles) . ':';
                                }
                            }
                        }
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                    'code' => $pdfTotal->getSourceField(),
                                    'label'=> $label,
                                    'amount'=>$sign*$salesObject->getDiscountAmount(),
                                    'baseAmount'=>$sign*$salesObject->getBaseDiscountAmount(),
                                    'discount_code'=>$order->getCouponCode()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     => Mage::helper('sales')->__('Discount') . ' '
                                    . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $sign * $salesObject->getDiscountAmount(),
                                'baseAmount'=> $sign * $salesObject->getBaseDiscountAmount(),
                                'discount_code'=>$order->getCouponCode()
                            );
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     => Mage::helper('sales')->__('Discount') . ' '
                                    . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $sign * $salesObject->getDiscountAmount()
                                    + $this->_hiddenTaxAmount,
                                'baseAmount'=> $sign * $salesObject->getBaseDiscountAmount()
                                    + $this->_baseHiddenTaxAmount,
                                'discount_code'=>$order->getCouponCode()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     => $label,
                                'amount'    => $sign * $salesObject->getDiscountAmount()
                                    + $this->_hiddenTaxAmount,
                                'baseAmount'=> $sign * $salesObject->getBaseDiscountAmount()
                                    + $this->_baseHiddenTaxAmount,
                                'discount_code'=>$order->getCouponCode()
                            );
                        }
                    }
                    break;
                case 'tax_amount':
                    //Prepare Tax
                    if (!$helper->displayTaxAmountWithGrandTotals()
                        && strtoupper($pdfTotal->getSortOrder()) != 'NO'
                    ) {
                        if ($salesObject->getTaxAmount() > 0 || Mage::getStoreConfig('tax/sales_display/zero_tax', $helper->getStoreId())) {
                            $filteredTaxrates = Mage::helper('pdfcustomiser')->getCalculatedTaxes($salesObject);
                            if (Mage::getStoreConfig('tax/sales_display/full_summary', $helper->getStoreId())
                                && $filteredTaxrates
                            ) {
                                foreach ($filteredTaxrates as $filteredTaxrate) {
                                    if ((strpos($filteredTaxrate['title'], "%") === false)
                                        && !is_null($filteredTaxrate['percent'])
                                    ) {
                                        $label = $filteredTaxrate['title'] . ' [' . sprintf(
                                            "%01.2f%%", $filteredTaxrate['percent']
                                        ) . ']';
                                    } else {
                                        $label = $filteredTaxrate['title'];
                                    }
                                    if (!is_null($filteredTaxrate['amount'])) {
                                        $label .= ':';
                                    } else {
                                        $label .= '&nbsp;';
                                    }
                                    $totals[$sortOrder][] = array(
                                        'label'      => $label,
                                        'amount'     => $filteredTaxrate['amount'],
                                        'baseAmount' => $filteredTaxrate['baseAmount']
                                    );
                                }
                            } else {
                                $totals[$sortOrder][] = array(
                                    'code'      => $pdfTotal->getSourceField(),
                                    'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                                    'amount'    => (float)$salesObject->getTaxAmount(),
                                    'baseAmount'=> (float)$salesObject->getBaseTaxAmount()
                                );
                            }
                        } elseif (
                            Mage::getStoreConfig(
                                'tax/sales_display/zero_tax', $helper->getStoreId()
                            )
                            && (float)$salesObject->getTaxAmount() == 0
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'       => $pdfTotal->getSourceField(),
                                'label'      => $helper->getTranslatedString('Tax', 'sales') . ":",
                                'amount'     => (float)0,
                                'baseAmount' => (float)0
                            );
                        }
                    }
                    break;
                case 'shipping_amount':
                    //Prepare Shipping
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if ($salesObject->getShippingInclTax() && $salesObject->getShippingInclTax() != 0) {
                            $shippingAmount = $salesObject->getShippingInclTax() - $salesObject->getShippingTaxAmount();
                            $baseShippingAmount
                                = $salesObject->getBaseShippingInclTax() - $salesObject->getBaseShippingTaxAmount();
                        } else {
                            $shippingAmount = $salesObject->getShippingAmount();
                            $baseShippingAmount = $salesObject->getBaseShippingAmount();
                        }
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling')) . ':',
                                'amount'    => $shippingAmount
                                    + $salesObject->getShippingTaxAmount(),
                                'baseAmount'=> $baseShippingAmount
                                    + $salesObject->getBaseShippingTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $shippingAmount
                                    + $salesObject->getShippingTaxAmount(),
                                'baseAmount'=> $baseShippingAmount
                                    + $salesObject->getBaseShippingTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $shippingAmount,
                                'baseAmount'=> $baseShippingAmount
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling')) . ':',
                                'amount'    => $shippingAmount,
                                'baseAmount'=> $baseShippingAmount
                            );
                        }
                    }
                    break;
                case 'adjustment_positive':
                    //Prepare AdjustmentPositive
                    if (
                        $salesObject instanceof Mage_Sales_Model_Order_Creditmemo
                        && $pdfTotal->canDisplay()
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        $totals[$sortOrder][] = array(
                            'code'      => $pdfTotal->getSourceField(),
                            'label'      => Mage::helper('sales')->__('Adjustment Refund') . ':',
                            'amount'     => $salesObject->getAdjustmentPositive(),
                            'baseAmount' => $salesObject->getBaseAdjustmentPositive()
                        );
                    }                    
                    break;
                case 'adjustment_negative':
                    //Prepare AdjustmentNegative
                    if (
                        $salesObject instanceof Mage_Sales_Model_Order_Creditmemo
                        && $pdfTotal->canDisplay()
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        $totals[$sortOrder][] = array(
                            'code'      => $pdfTotal->getSourceField(),
                            'label'      => Mage::helper('sales')->__('Adjustment Fee') . ':',
                            'amount'     => $salesObject->getAdjustmentNegative(),
                            'baseAmount' => $salesObject->getBaseAdjustmentNegative()
                        );
                    }                    
                    break;
                case 'surcharge_amount':
                    $amount = $pdfTotal->getAmount();
                    if ($amount != 0) {
                        $totals[$sortOrder][] = array(
                            'code'       => $pdfTotal->getSourceField(),
                            'label'      => Mage::helper('sagepaysuite')->__($pdfTotal->getTitle()) . ':',
                            'amount'     => $amount,
                            'baseAmount' => $amount //no base amount available
                        );
                    }

                    break;
                case 'fooman_surcharge_amount':
                    //Prepare Fooman Surcharge
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                    'code' => $pdfTotal->getSourceField(),
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount()
                                        + $salesObject->getFoomanSurchargeTaxAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                                        + $salesObject->getBaseFoomanSurchargeTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                    'code' => $pdfTotal->getSourceField(),
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount()
                                        + $salesObject->getFoomanSurchargeTaxAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                                        + $salesObject->getBaseFoomanSurchargeTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                    'code' => $pdfTotal->getSourceField(),
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                    'code' => $pdfTotal->getSourceField(),
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                            );
                        }
                    }                    
                    break;
                case 'customer_credit_amount':
                    //Prepare MageWorx Customer Credit
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = $pdfTotal->getAmountPrefix() == '-'?-1:1;
                        $sortOrder = $pdfTotal->getSortOrder();
                        $totals[$sortOrder][] = array(
                            'code' => $pdfTotal->getSourceField(),
                            'label'=>Mage::helper('customercredit')->__('Internal Credit').':',
                            'amount'=>$sign*$salesObject->getCustomerCreditAmount(),
                            'baseAmount'=>$sign*$salesObject->getBaseCustomerCreditAmount()
                        );
                    }
                    break;
                case 'shipping_and_handling_tax':
                    if(strtoupper($pdfTotal->getSortOrder())!='NO'){
                        $taxHelper = Mage::helper('tax');
                        if (method_exists($taxHelper, 'getShippingTax')) {
                            $shippingTaxes = $taxHelper->getShippingTax($salesObject);
                            if ($shippingTaxes) {
                                foreach ($shippingTaxes as $shippingTax) {
                                    $totals[$sortOrder][] = array(
                                        'code'      => $shippingTax['title'],
                                        'label'     => str_replace(
                                            ' &amp; ', ' & ', $shippingTax['title']
                                        ) . ':',
                                        'amount'    =>  $shippingTax['tax_amount'],
                                        'baseAmount'=> $shippingTax['base_tax_amount']
                                    );
                                }
                            }
                        }
                    }
                    break;

                case 'customer_balance_amount':
                    //Prepare Enterprise Store Credit
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && (float)$salesObject->getCustomerBalanceAmount() !=0) {
                        $sign = ((float)$salesObject->getCustomerBalanceAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'code'      => $pdfTotal->getSourceField(),
                            'label'     => str_replace(
                                ' &amp; ', ' & ', Mage::helper('core')->__('Store Credit')
                            ) . ':',
                            'amount'    => $sign * $salesObject->getCustomerBalanceAmount(),
                            'baseAmount'=> $sign * $salesObject->getBaseCustomerBalanceAmount()
                        );
                    }
                    break;
                case 'gift_cards_amount':
                    //Prepare Enterprise Gift Cards
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $salesObject->getGiftCardsAmount()!=0) {
                        $sign = ((float)$salesObject->getGiftCardsAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'code'      => $pdfTotal->getSourceField(),
                            'label'     =>
                            str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftcardaccount')->__('Gift Cards'))
                                . ':',
                            'amount'    => $sign * $salesObject->getGiftCardsAmount(),
                            'baseAmount'=> $sign * $salesObject->getBaseGiftCardsAmount()
                        );
                    }
                    break;
                case 'gw_price':
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $salesObject->getGwPrice() !=0) {
                        if (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order')) . ':',
                                'amount'    => $salesObject->getGwPrice()
                                    + $order->getGwTaxAmount(),
                                'baseAmount'=> $salesObject->getGwBasePrice()
                                    + $order->getGwBaseTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $salesObject->getGwPrice()
                                    + $order->getGwTaxAmount(),
                                'baseAmount'=> $salesObject->getGwBasePrice()
                                    + $order->getGwBaseTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $salesObject->getGwPrice(),
                                'baseAmount'=> $salesObject->getGwBasePrice()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order')) . ':',
                                'amount'    => $salesObject->getGwPrice(),
                                'baseAmount'=> $salesObject->getGwBasePrice()
                            );
                        }
                    }
                    break;
                case 'gw_items_price':
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $salesObject->getGwItemsPrice() !=0) {
                        if (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items')) . ':',
                                'amount'    => $salesObject->getGwItemsPrice()
                                    + $order->getGwItemsTaxAmount(),
                                'baseAmount'=> $salesObject->getGwItemsBasePrice()
                                    + $order->getGwItemsBaseTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $salesObject->getGwItemsPrice()
                                    + $order->getGwItemsTaxAmount(),
                                'baseAmount'=> $salesObject->getGwItemsBasePrice()
                                    + $order->getGwItemsBaseTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $salesObject->getGwItemsPrice(),
                                'baseAmount'=> $salesObject->getBaseItemsGwPrice()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items')) . ':',
                                'amount'    => $salesObject->getGwItemsPrice(),
                                'baseAmount'=> $salesObject->getGwItemsBasePrice()
                            );
                        }
                    }
                    break;
                case 'gw_card_price':
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $salesObject->getGwCardPrice() !=0) {
                        if (Mage::getStoreConfig('tax/sales_display/printed_card', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card')) . ':',
                                'amount'    => $salesObject->getGwCardPrice()
                                    + $order->getGwCardTaxAmount(),
                                'baseAmount'=> $salesObject->getGwCardBasePrice()
                                    + $order->getGwCardBaseTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/printed_card', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $salesObject->getGwCardPrice()
                                    + $order->getGwCardTaxAmount(),
                                'baseAmount'=> $salesObject->getGwCardBasePrice()
                                    + $order->getGwCardBaseTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $salesObject->getGwCardPrice(),
                                'baseAmount'=> $salesObject->getGwCardBasePrice()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card')) . ':',
                                'amount'    => $salesObject->getGwCardPrice(),
                                'baseAmount'=> $salesObject->getGwCardBasePrice()
                            );
                        }
                    }
                    break;
                case 'gw_combined':
                    $gwCombined= $salesObject->getGwPrice() + $salesObject->getGwItemsPrice() + $salesObject->getGwCardPrice();
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $gwCombined != 0) {
                        $baseGwCombined= $salesObject->getGwBasePrice() + $salesObject->getGwItemsBasePrice() + $salesObject->getGwCardBasePrice();

                        $GwTaxCombined = $salesObject->getGwTaxAmount() + $salesObject->getGwItemsTaxAmount() + $salesObject->getGwCardTaxAmount();
                        $baseTaxGwCombined = $salesObject->getGwBaseTaxAmount() + $salesObject->getGwItemsBaseTaxAmount() + $salesObject->getGwCardBaseTaxAmount();
                        if (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping')) . ':',
                                'amount'    => $gwCombined + $GwTaxCombined,
                                'baseAmount'=> $baseGwCombined + $baseTaxGwCombined
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $gwCombined + $GwTaxCombined,
                                'baseAmount'=> $baseGwCombined + $baseTaxGwCombined
                            );
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $gwCombined,
                                'baseAmount'=> $baseGwCombined
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'code'      => $pdfTotal->getSourceField(),
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping')) . ':',
                                'amount'    => $gwCombined,
                                'baseAmount'=> $baseGwCombined
                            );
                        }
                    }
                    break;
                case 'reward_currency_amount':
                    //Prepare Enterprise paid from reward points
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && (float)$salesObject->getRewardCurrencyAmount() !=0) {
                        $sign = ((float)$salesObject->getRewardCurrencyAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'code'      => $pdfTotal->getSourceField(),
                            'label'     => str_replace(
                                ' &amp; ',
                                ' & ',
                                Mage::helper('enterprise_reward')->formatReward($salesObject->getRewardPointsBalance())
                            ) . ':',
                            'amount'    => $sign * $salesObject->getRewardCurrencyAmount(),
                            'baseAmount'=> $sign * $salesObject->getBaseRewardCurrencyAmount()
                        );
                    }
                    break;
                case 'money_for_points':
                    //Aheadworks Points extension
                    if ($salesObject->getMoneyForPoints() != 0) {
                        $sign = ((float)$salesObject->getMoneyForPoints() > 0) ? -1 : 1;
                        $totals[$sortOrder][] = array(
                            'code'       => $pdfTotal->getSourceField(),
                            'label'      => str_replace(
                                    ' &amp; ',
                                    ' & ',
                                    Mage::helper('points')->__('%s', Mage::helper('points/config')->getPointUnitName())
                                ) . ':',
                            'amount'     => $sign * $order->getMoneyForPoints(),
                            'baseAmount' => $sign * $order->getBaseMoneyForPoints()
                        );
                    }
                    break;
                case 'giftcert_amount':
                    //Unirgy Giftcert Extension
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getGiftcertAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'code'      => $pdfTotal->getSourceField(),
                            'label'     => str_replace(
                                ' &amp; ',
                                ' & ',
                                Mage::helper('ugiftcert')->__('Gift Certificates (%s)', $order->getGiftcertCode())
                            ) . ':',
                            'amount'    => $sign * $order->getGiftcertAmount(),
                            'baseAmount'=> $sign * $order->getBaseGiftcertAmount()
                        );
                    }
                    break;
                case 'klarnaPaymentModule':
                    //Prepare Klarna-Faktura Invoice fee(separate extension by trollweb_kreditor)
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder()) != 'NO') {
                        $klarnaAmounts = $pdfTotal->getAmount();
                        $totals[$sortOrder][] = array(
                            'code'       => $pdfTotal->getSourceField(),
                            'label'      => $pdfTotal->getTitle() . ':',
                            'amount'     => $klarnaAmounts['incl'],
                            'baseAmount' => Mage::app()->getStore()->roundPrice(
                                $klarnaAmounts['incl'] * $order->getBaseToOrderRate()
                            )
                        );
                    }
                    break;
                case 'msp_cashondelivery':
                    //Prepare MSP_CashOnDelivery
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder()) != 'NO') {
                        $amount = $order->getMspCashondelivery();
                        $totals[$sortOrder][] = array(
                            'code'       => $pdfTotal->getSourceField(),
                            'label'      => Mage::helper('msp_cashondelivery')->__('Cash On Delivery') . ':',
                            'amount'     => $amount,
                            'baseAmount' => Mage::app()->getStore()->roundPrice(
                                $amount * $order->getBaseToOrderRate()
                            )
                        );
                    }
                    break;
                case 'cod_fee':
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder()) != 'NO') {
                        $tmpPdfTotalAmounts = $pdfTotal->getTotalsForDisplay();
                        foreach ($tmpPdfTotalAmounts as $tmpPdfTotalAmount) {
                            $totals[$sortOrder][] = array(
                                'code'       => $pdfTotal->getSourceField(),
                                'label'      => $tmpPdfTotalAmount['label'],
                                'amount'     => $pdfTotal->getAmount(),
                                'baseAmount' => Mage::app()->getStore()->roundPrice(
                                        $pdfTotal->getAmount() * $order->getBaseToOrderRate()
                                    )
                            );
                        }
                    }
                    break;
                case 'customer_balance_total_refunded':
                case 'customer_bal_total_refunded':
                    //dealt with separately
                    break;
                case 'grand_total':
                    //dealt with separately
                    break;
                default:
                    //unknown total
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $tmpPdfTotalAmounts = $pdfTotal->getTotalsForDisplay();
                        if (isset($tmpPdfTotalAmounts['amount'])) {
                            $tmpPdfTotalAmount = $tmpPdfTotalAmounts['amount'];
                            $sign = ($tmpPdfTotalAmount > 0) ? 1 : -1;
                            $totals[$sortOrder][] = array(
                                'code'       => $pdfTotal->getSourceField(),
                                'label'      => $pdfTotal->getTitle(),
                                'amount'     => $sign * $tmpPdfTotalAmount,
                                'baseAmount' => Mage::app()->getStore()->roundPrice(
                                    $sign * $tmpPdfTotalAmount * $order->getBaseToOrderRate()
                                )
                            );
                        } elseif (is_array($tmpPdfTotalAmounts)) {
                            foreach ($tmpPdfTotalAmounts as $tmpPdfTotalAmount) {
                                if (Mage::helper('core')->isModuleEnabled('TBT_Rewards')
                                    && $tmpPdfTotalAmount['label'] == Mage::helper('rewards')->__("Item Discounts")
                                ) {
                                    $tmpTotalAmount = $salesObject->getRewardsDiscountAmount();
                                    $tmpBaseTotalAmount = Mage::app()->getStore()->roundPrice(
                                        $tmpTotalAmount * $order->getBaseToOrderRate()
                                    );
                                    $tmpPdfTotalAmount['label'] = $tmpPdfTotalAmount['label'] . ': ';
                                } elseif (method_exists(get_class($pdfTotal), 'getAmount') && !is_array($pdfTotal->getAmount()) && !is_object($pdfTotal->getAmount())) {
                                    $tmpTotalAmount = $pdfTotal->getAmount();
                                    $tmpBaseTotalAmount = Mage::app()->getStore()->roundPrice(
                                        $tmpTotalAmount * $order->getBaseToOrderRate()
                                    );
                                } else {
                                    $tmpTotalAmount = $tmpPdfTotalAmount['amount'];
                                    if (isset($tmpPdfTotalAmount['base_amount'])) {
                                        $tmpBaseTotalAmount = $tmpPdfTotalAmount['base_amount'];
                                    } else {
                                        //since the amount above is already converted to a string we can't convert
                                        $tmpBaseTotalAmount = $tmpPdfTotalAmount['amount'];
                                    }
                                }
                                if ($tmpTotalAmount != 0) {
                                    $totals[$sortOrder][] = array(
                                        'code'      =>  $pdfTotal->getSourceField(),
                                        'label'      => $tmpPdfTotalAmount['label'],
                                        'amount'     => $tmpTotalAmount,
                                        'baseAmount' => $tmpBaseTotalAmount
                                    );
                                }
                            }
                        }
                    }
                    break;
            }
        }

        //support Mico Rushprocessing
        if ((float)$this->getOrder($salesObject)->getMicoRushprocessingprice() > 0) {
            $totals[$sortOrder][] = array(
                'code' => $pdfTotal->getSourceField(),
                'label'=>'Product &amp; Packaging:',
                'amount'=>(float)$this->getOrder($salesObject)->getMicoRushprocessingprice(),
                'baseAmount'=>(float)$this->getOrder($salesObject)->getMicoRushprocessingprice()
            );
        }
        
        //support payment fee by XIB
        //use same settings as shipping (total does not provide separate settings)       
        if ((float)$salesObject->getXibpaymentsFee()) {
            $xibTotal = Mage::helper('xibpayments/pdfcustomiser')->appendTotals(
                $totals[$sortOrder], $salesObject, $order, $helper->getStoreId()
            );
            $xibTotal['code'] = 'xibfee';
            $totals[550][] = $xibTotal;
        }

        //Grand Total
        $grandTotals = array();
        
        if (Mage::getStoreConfigFlag('sales_pdf/all/allonly1grandtotal', $helper->getStoreId())) {
            $grandTotals[] = array(
                'code'      => 'grand_total',
                'label'      => Mage::helper('sales')->__('Grand Total') . ':',
                'amount'     => $salesObject->getGrandTotal(),
                'baseAmount' => $salesObject->getBaseGrandTotal(),
                'bold'       => true
            );            
        } elseif (Mage::getStoreConfig('tax/sales_display/grandtotal', $helper->getStoreId())) {
            $grandTotals[] = array(
                'code'      => 'grand_total',
                'label'      => Mage::helper('sales')->__('Grand Total')
                    . ' (' . Mage::helper('tax')->__('Excl. Tax') . '):',
                'amount'     => $salesObject->getGrandTotal() - $salesObject->getTaxAmount(),
                'baseAmount' => $salesObject->getBaseGrandTotal() - $salesObject->getBaseTaxAmount(),
                'bold'       => true
            );
            if ((float)$salesObject->getTaxAmount() > 0 || Mage::getStoreConfig('tax/sales_display/zero_tax', $helper->getStoreId())) {
                $filteredTaxrates = Mage::helper('pdfcustomiser')->getCalculatedTaxes($salesObject);
                //Magento looses information of tax rates if an order is split into multiple invoices
                //so only display summary if both tax amounts equal
                if (Mage::getStoreConfig('tax/sales_display/full_summary', $helper->getStoreId())
                    && $filteredTaxrates
                ) {
                    foreach ($filteredTaxrates as $filteredTaxrate) {
                        $grandTotals[] = array(
                            'code'      => 'tax_amount',
                            'label'      => $filteredTaxrate['title'] . ':',
                            'amount'     => (float)$filteredTaxrate['amount'],
                            'baseAmount' => (float)$filteredTaxrate['baseAmount'],
                            'bold'       => false
                        );
                    }
                } else {
                    $grandTotals[] = array(
                        'code'      => 'tax_amount',
                        'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                        'amount'    => (float)$salesObject->getTaxAmount(),
                        'baseAmount'=> (float)$salesObject->getBaseTaxAmount(),
                        'bold'      => false
                    );
                }
            } elseif (Mage::getStoreConfig('tax/sales_display/zero_tax', $helper->getStoreId())) {
                    $grandTotals[] = array(
                        'code'      => 'tax_amount',
                        'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                        'amount'    => 0,
                        'baseAmount'=> 0,
                        'bold'      => false
                    );
            }
            $grandTotals[] = array(
                    'code'      => 'grand_total',
                    'label'=> Mage::helper('sales')->__('Grand Total'). ' ('.Mage::helper('tax')->__('Incl. Tax').'):',
                    'amount'    => $salesObject->getGrandTotal(),
                    'baseAmount'=> $salesObject->getBaseGrandTotal(),
                    'bold'      => true
            );
        } else {
            $grandTotals[] = array(
                'code'      => 'grand_total',
                'label'     => Mage::helper('sales')->__('Grand Total') . ':',
                'amount'    => $salesObject->getGrandTotal() - $salesObject->getTaxAmount(),
                'baseAmount'=> $salesObject->getBaseGrandTotal() - $salesObject->getBaseTaxAmount(),
                'bold'      => true
            );            
        }

        //Enterprise output refunded to store credit
        if ((float)$salesObject->getCustomerBalanceTotalRefunded()) {
            $grandTotals[] = array(
                'code'      => 'customer_balance_total_refunded',
                'label'     => Mage::helper('enterprise_giftcardaccount')->__('Refunded to Store Credit') . ':',
                'amount'    => $salesObject->getCustomerBalanceTotalRefunded(),
                'baseAmount'=> $salesObject->getCustomerBalanceTotalRefunded(),
                'bold'      => true
            );
        }
        
        ksort($totals);
        $totalsSorted = array();
        foreach ($totals as $sortOrder) {
            foreach ($sortOrder as $total) {
                $formattedTotal = $total;
                $formattedTotal['label'] = htmlentities($formattedTotal['label'], ENT_QUOTES, 'UTF-8', false);
                $formattedTotal['amount_default'] = $this->formatPrice($helper, $order, $total['amount']);
                $formattedTotal['amount'] = $this->formatPrice($helper, $order, $total['amount']);
                $formattedTotal['base_amount'] = $this->formatPrice($helper, $order, $total['baseAmount'], 'base');
                $totalsSorted['totals'][] = $formattedTotal;
            }           
        }        
        foreach ($grandTotals as $total) {
            $formattedTotal = $total;
            $formattedTotal['label'] = htmlentities($formattedTotal['label'], ENT_QUOTES, 'UTF-8', false);
            $formattedTotal['amount_default'] = $this->formatPrice($helper, $order, $total['amount']);
            $formattedTotal['amount'] = $this->formatPrice($helper, $order, $total['amount']);
            $formattedTotal['base_amount'] = $this->formatPrice($helper, $order, $total['baseAmount'], 'base');
            $totalsSorted['grand_totals'][] = $formattedTotal;
        }
        if (!isset($totalsSorted['totals'])) {
            $totalsSorted['totals'] = array();
        }
        $transport = new Varien_Object();
        $transport->setTotals($totalsSorted);
        Mage::dispatchEvent(
            'fooman_pdfcustomiser_totals',
            array(
                'transport' => $transport
            )
        );

        return $transport->getTotals();
    }

    /**
     * format the price according to locale settings
     *
     * @param      $helper
     * @param      $order
     * @param      $price
     * @param null $currency
     *
     * @return string
     */
    public function formatPrice($helper, $order, $price, $currency = null)
    {
        return $helper->formatPrice($order, $price, $currency);
    }
    
}
