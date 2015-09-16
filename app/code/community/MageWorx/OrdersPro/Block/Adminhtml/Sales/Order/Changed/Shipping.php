<?php

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Changed_Shipping extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    protected $_template = 'mageworx/orderspro/changed/shipping.phtml';

    public function getActiveMethodRate()
    {
        $quote = $this->getQuote();
        $rates = $quote->getShippingAddress()->getGroupedAllShippingRates();
        $method = $quote->getShippingAddress()->getShippingMethod();

        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $code => $rate) {
                    if ($rate->getCode() == $method) {
                        return $rate;
                    }
                }
            }
        }
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $this->getOrder()->getStoreId())) {
            return $name;
        }
        return $carrierCode;
    }

    public function getShippingPrice()
    {
        return $this->getQuote()->getStore()->convertPrice(
            $this->getQuote()->getShippingAddress()->getShippingAmount(),
            true
        );
    }
}