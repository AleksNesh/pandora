<?php

class Magestore_Giftwrap_Model_Shipping_Carrier_Freeshipping extends Mage_Shipping_Model_Carrier_Freeshipping {

    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        $ischeck = Mage::getStoreConfig('giftwrap/general/add_product_price');
        if ($ischeck) {
            $giftwrapAmount = Mage::helper('giftwrap')->giftwrapAmount();
        } else {
            $giftwrapAmount = 0;
        }
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $result = Mage::getModel('shipping/rate_result');
//      $packageValue = $request->getBaseCurrency()->convert($request->getPackageValueWithDiscount(), $request->getPackageCurrency());
        $packageValue = $request->getPackageValueWithDiscount();

        $this->_updateFreeMethodQuote($request);

        $allow = ($request->getFreeShipping()) || ($packageValue + $giftwrapAmount >= $this->getConfigData('free_shipping_subtotal'));

        if ($allow) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('freeshipping');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('freeshipping');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $result->append($method);
        }

        return $result;
    }

    /**
     * Allows free shipping when all product items have free shipping (promotions etc.)
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return void
     */
}