<?php

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Changed_Address extends Mage_Adminhtml_Block_Template
{
    protected $_template = 'mageworx/orderspro/changed/address.phtml';

    public function getAddress()
    {
        $quote = $this->getQuote();
        $address = ($this->getAddressType() == 'shipping') ? $quote->getShippingAddress() : $quote->getBillingAddress();

        return $address;
    }
}