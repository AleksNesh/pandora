<?php

class Magestore_Giftwrap_Model_Order_Pdf_Amount extends Mage_Sales_Model_Order_Pdf_Total_Default {

    public function getTotalsForDisplay() {
        $invoiceId = Mage::app()->getRequest()->getParam('invoice_id');
        $creditmemoId = Mage::app()->getRequest()->getParam('creditmemo_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $amount = $this->getOrder()->formatPriceTxt($invoice->getGiftwrapAmount());
            $tax = $this->getOrder()->formatPriceTxt($invoice->getGiftwrapTax());
        } else if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            $amount = $this->getOrder()->formatPriceTxt($creditmemo->getGiftwrapAmount());
            $tax = $this->getOrder()->formatPriceTxt($creditmemo->getGiftwrapTax());
        } else {
            $amount = $this->getOrder()->formatPriceTxt($this->getOrder()->getGiftwrapAmount());
            $tax = $this->getOrder()->formatPriceTxt($this->getOrder()->getGiftwrapTax());
        }

       
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $totals = array(array(
                'label' => Mage::helper('giftwrap')->__('Giftwrap Amount'),
                'amount' => $amount,
                'font_size' => $fontSize,
            )
        );

        
        if ($this->getAmountPrefix())
            $tax = $this->getAmountPrefix() . $tax;
        $totals[] = array(
            'label' => Mage::helper('giftwrap')->__('Giftwrap Tax'),
            'amount' => $tax,
            'font_size' => $fontSize,
        );


        return $totals;
    }

}
