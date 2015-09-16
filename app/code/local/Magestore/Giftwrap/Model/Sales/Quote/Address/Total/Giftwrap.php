<?php

class Magestore_Giftwrap_Model_Sales_Quote_Address_Total_Giftwrap extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $active = Mage::helper('giftwrap')->enableGiftwrap();
        
        if (!$active) {
            return;
        }
        
        if(Mage::app()->getStore()->isAdmin())
        {
            return $this;
        }
        
        $items = $address->getAllItems();

        if (!count($items)) {
            return $this;
        }

        $address_id = $address->getId();
        /*
         * 	update of version 0.2.2
         */
        $giftwrapAmount = Mage::helper('giftwrap')->giftwrapAmount(null, $address_id);
        $address->setGiftwrapAmount($giftwrapAmount);
        $address->setBaseGiftwrapAmount(0);
        //var_dump($address->getData());die();
        // if($giftwrapAmount>0){
        // $taxAmount = 10;
        // $address->setTaxAmount($address->getTaxAmount() + $taxAmount);
        // $address->setBaseTaxAmount($address->getBaseTaxAmount() + $taxAmount);
        // }
        Mage::getModel('core/session')->setData('giftwrap_amount', $giftwrapAmount);

        $address->setGrandTotal($address->getGrandTotal() + $address->getGiftwrapAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getGiftwrapAmount());
        //var_dump($address->getData());die();
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $active = Mage::helper('giftwrap')->enableGiftwrap();
        if (!$active) {
            return;
        }
        $amount = $address->getGiftwrapAmount();
        $title = Mage::helper('sales')->__('Gift Wrap');
        if ($amount != 0) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }

    public function getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function getQuote() {
        return $this->getCheckoutSession()->getQuote();
    }

    public function getProductName($itemId) {
        $item = $this->getQuote()->getItemById($itemId);
        if ($item) {
            return $item->getProduct()->getName();
        } else {
            return '';
        }
    }

}
