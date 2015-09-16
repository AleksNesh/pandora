<?php

class Magestore_Giftwrap_Model_Sales_Quote_Address_Total_Giftwraptax extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $active = Mage::helper('giftwrap')->enableGiftwrap();
        if (!$active) {
            return;
        }
        if (Mage::app()->getStore()->isAdmin()) {
            return $this;
        }
        $quote = $address->getQuote();
        $items = $quote->getAllItems();
        if (!count($items)) {
            return $this;
        }

        /*
         * 	update of version 2.0.2
         */
        if (!Mage::getStoreConfig('giftwrap/calculation/tax', Mage::app()->getStore(true)->getId())) {
            return;
        }
        //-------------------------
        $items = $this->_getAddressItems($address);
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $this->_store
        );
        $giftwrapTax = 0;
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $request->setProductClassId($child->getProduct()->getTaxClassId());
                }
            } else {
                $request->setProductClassId($item->getProduct()->getTaxClassId());
            }
            //$quoteId = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('entity_id', $address->getOrderId())->getFirstItem()->getQuoteId();
            $productId = $item->getProductId();
            $quoteItemId = $item->getItemId();
            $selectionItem = Mage::getResourceModel('giftwrap/selectionitem_collection')->addFieldToFilter('item_id', $quoteItemId)->getFirstItem();
            $selectionItemId = $selectionItem->getSelectionId();
            $rate = Mage::getSingleton('tax/calculation')->getRate($request);
            if (isset($selectionItemId)) {
                $giftwrapId = Mage::getResourceModel('giftwrap/selection_collection')
                                ->addFieldToFilter('id', $selectionItemId)
                                ->getFirstItem()->getStyleId();
                if ($giftwrapId) {
                    if ($selectionItem->getCalculateOnItem() == '1')
                        $giftwrapPrice = Mage::getResourceModel('giftwrap/giftwrap_collection')->addFieldToFilter('giftwrap_id', $giftwrapId)->getFirstItem()->getPrice() * $item->getQty();
                    else
                        $giftwrapPrice = Mage::getResourceModel('giftwrap/giftwrap_collection')->addFieldToFilter('giftwrap_id', $giftwrapId)->getFirstItem()->getPrice();
                }
                $giftcardId = Mage::getResourceModel('giftwrap/selection_collection')->addFieldToFilter('id', $selectionItemId)->getFirstItem()->getGiftcardId();
                if ($giftcardId) {
                    if ($selectionItem->getCalculateOnItem() == '1')
                        $giftcardPrice = Mage::getResourceModel('giftwrap/giftcard_collection')->addFieldToFilter('giftcard_id', $giftcardId)->getFirstItem()->getPrice() * $item->getQty();
                    else
                        $giftcardPrice = Mage::getResourceModel('giftwrap/giftcard_collection')->addFieldToFilter('giftcard_id', $giftcardId)->getFirstItem()->getPrice();
                }
                $giftwrapAmountItem = $giftwrapPrice + $giftcardPrice;
                $giftwrapTaxItem = $giftwrapAmountItem * $rate / 100;
                $item->setGiftwrapTax($giftwrapTaxItem);              
                 $giftwrapTax += $giftwrapTaxItem;
            }            
        }       
        Mage::getModel('core/session')->setData('giftwrap_rate', $rate);
        //---------------------------
        // $giftwrapAmount = Mage::helper('giftwrap')->giftwrapAmount();

        $address->setGiftwrapTax($giftwrapTax);
        $address->setBaseGiftwrapTax(0);
        Mage::getModel('core/session')->setData('giftwrap_tax', $giftwrapTax);
        $address->setGrandTotal($address->getGrandTotal() + $giftwrapTax);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $giftwrapTax);

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $active = Mage::helper('giftwrap')->enableGiftwrap();
        if (!$active) {
            return;
        }

        $amount = $address->getGiftwrapTax();
        $title = Mage::helper('sales')->__('Gift Wrap Tax');
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
