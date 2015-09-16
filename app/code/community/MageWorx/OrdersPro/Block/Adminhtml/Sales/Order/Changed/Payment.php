<?php

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Changed_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
{
    protected function _beforeToHtml()
    {
        $this->setPayment($this->getQuote()->getPayment());
        return $this;
    }
}