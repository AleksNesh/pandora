<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_Sales_Order_Invoice_Shipping extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect shipping amount to be invoiced based on already invoiced amount
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $previousInvoices = $invoice->getOrder()->getInvoiceCollection();

        if ($invoice->getShippingAmount() > 0) {
            return $this;
        }

        $order = $invoice->getOrder();

        $shippingAmount        = $order->getShippingAmount() - $order->getShippingInvoiced() - $order->getShippingRefunded();
        $baseShippingAmount    = $order->getBaseShippingAmount() - $order->getBaseShippingInvoiced() - $order->getBaseShippingRefunded();

        $invoice->setShippingAmount($shippingAmount);
        $invoice->setBaseShippingAmount($baseShippingAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $shippingAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseShippingAmount);

        return $this;
    }
}