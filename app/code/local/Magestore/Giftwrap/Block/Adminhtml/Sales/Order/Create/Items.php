<?php

class Magestore_GiftWrap_Block_Adminhtml_Sales_Order_Create_Items extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract {

    public function getGiftboxCollection() {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $collection = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('quote_id', $quote->getId())
        ;
        return $collection;
    }

    public function getParentId($product) {
        if ($product->getVisibility() == '1') {
            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId()); // check for grouped product
            if (!$parentIds)
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId()); //check for config product
        }
        return $parentIds;
    }

}
