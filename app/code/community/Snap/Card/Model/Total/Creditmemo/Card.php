<?php

class Snap_Card_Model_Total_Creditmemo_Card extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Collect snap gift card account totals for credit memo
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getBaseSnapCardsAmount() && $order->getBaseSnapCardsInvoiced() != 0) {
            $gcaLeft = $order->getBaseSnapCardsInvoiced() - $order->getBaseSnapCardsRefunded();

            $used = 0;
            $baseUsed = 0;

            if ($gcaLeft >= $creditmemo->getBaseGrandTotal()) {
                $baseUsed = $creditmemo->getBaseGrandTotal();
                $used = $creditmemo->getGrandTotal();

                $creditmemo->setBaseGrandTotal(0);
                $creditmemo->setGrandTotal(0);

                $creditmemo->setAllowZeroGrandTotal(true);
            } else {
                $baseUsed = $order->getBaseSnapCardsInvoiced() - $order->getBaseSnapCardsRefunded();
                $used = $order->getSnapCardsInvoiced() - $order->getSnapCardsRefunded();

                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()-$baseUsed);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal()-$used);
            }

            $creditmemo->setBaseSnapCardsAmount($baseUsed);
            $creditmemo->setSnapCardsAmount($used);
        }

        $creditmemo->setBaseCustomerBalanceReturnMax($creditmemo->getBaseCustomerBalanceReturnMax() + $creditmemo->getBaseSnapCardsAmount());

        $creditmemo->setCustomerBalanceReturnMax($creditmemo->getCustomerBalanceReturnMax() + $creditmemo->getSnapCardsAmount());
        $creditmemo->setCustomerBalanceReturnMax($creditmemo->getCustomerBalanceReturnMax() + $creditmemo->getGrandTotal());

        return $this;
    }
}
