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
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_Sales_Order_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $payment = $invoice->getOrder()->getPayment();
        
        $paymentData = Mage::app()->getRequest()->getPost('payment');
        if ($paymentData && isset($paymentData['cc_number'])) return $this;
        
        if (($payment->getMethod()=='authorizenet' || $payment->getMethod()=='authorizenet_directpost' || $payment->getMethod()=='paypal_direct') && $payment->getBaseAmountOrdered()>0 && $invoice->getBaseGrandTotal()!=$payment->getBaseAmountOrdered()) {
            $invoice->setGrandTotal($payment->getAmountOrdered());
            $invoice->setBaseGrandTotal($payment->getBaseAmountOrdered());
            $invoice->setOrdersProFlag(true);
        }
        return $this;
    }
}