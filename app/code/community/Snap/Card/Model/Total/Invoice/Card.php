<?php

class Snap_Card_Model_Total_Invoice_Card extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect snap gift card account totals for invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getBaseSnapCardsAmount() && $order->getBaseSnapCardsInvoiced() != $order->getBaseSnapCardsAmount()) {
            $gcaLeft = $order->getBaseSnapCardsAmount() - $order->getBaseSnapCardsInvoiced();
            $used = 0;
            $baseUsed = 0;
            if ($gcaLeft >= $invoice->getBaseGrandTotal()) {
                $baseUsed = $invoice->getBaseGrandTotal();
                $used = $invoice->getGrandTotal();

                $invoice->setBaseGrandTotal(0);
                $invoice->setGrandTotal(0);
            } else {
                $baseUsed = $order->getBaseSnapCardsAmount() - $order->getBaseSnapCardsInvoiced();
                $used = $order->getSnapCardsAmount() - $order->getSnapCardsInvoiced();

                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()-$baseUsed);
                $invoice->setGrandTotal($invoice->getGrandTotal()-$used);
            }

            $invoice->setBaseSnapCardsAmount($baseUsed);
            $invoice->setSnapCardsAmount($used);
        }
        return $this;
    }
}
