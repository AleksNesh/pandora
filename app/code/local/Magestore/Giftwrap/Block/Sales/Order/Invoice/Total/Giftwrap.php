<?php

class Magestore_Giftwrap_Block_Sales_Order_Invoice_Total_Giftwrap extends Mage_Core_Block_Template {

    public function initTotals() {

        $totalsBlock = $this->getParentBlock();
        $invoice = $totalsBlock->getInvoice();
        //zend_debug::dump($invoice->getGiftwrapAmount());die();
        if ($invoice->getGiftwrapAmount() > 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => 'giftwrapamount',
                'label' => $this->__('Gift Wrap Amount:'),
                'value' => $invoice->getGiftwrapAmount(),
                    )), 'subtotal');
        }
    }

}