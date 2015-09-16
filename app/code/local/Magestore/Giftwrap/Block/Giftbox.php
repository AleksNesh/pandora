<?php

class Magestore_Giftwrap_Block_Giftbox extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    //add by Sally
    public function getAllPapers() {
        $papers = Mage::getModel('giftwrap/giftwrap')
                ->getCollection()
                ->addFieldToFilter(
                        'store_id', Mage::app()->getStore()->getId())
                ->addFieldToFilter('status', 1)
        ;
        $list = array();
        foreach ($papers as $paper) {
            $list[] = $paper;
        }
        return $list;
    }
    public function getAllGiftcards() {
        $gifcards = Mage::getModel('giftwrap/giftcard')->getCollection()
                ->addFieldToFilter(
                        'store_id', Mage::app()->getStore()
                        ->getId())
                ->addFieldToFilter('status', 1);
        return $gifcards;
    }
    //end
    public function getGiftboxCollection() {
        $storeId = Mage::app()->getStore()->getId();
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $collection = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('quote_id', $quote->getId())
        ;
        $selection_ids = array();
        if (count($collection) && !Mage::helper('customer')->isLoggedIn()) {
            foreach ($collection as $value) {
                $selection_ids[] = $value->getId();
            }
            $cache = array();

            $items = $quote->getItemsCollection();
            foreach ($items as $item) {
                $productId = $item->getProduct()->getId();
                $cache[$item->getId()] = $productId;
            }
            $object = new Varien_Object();
            $object->setData('item_list', $cache);
            Mage::getModel('core/session')->setData('cache', $cache);
            Mage::getModel('core/session')->setData('quote_giftwrap', $quote->getId());
        }
        return $collection;
    }

   public function getGiftboxInOrder($order) {

        $itemcollection = $order->getItemsCollection();
        $itemGiftvoucherRenderer = $this->getLayout()->getBlock('items')->getItemRenderer('giftvoucher');
        $itemDefaultRenderer = $this->getLayout()->getBlock('items')->getItemRenderer();
        $item_ids = array();
        if ($itemGiftvoucherRenderer->getItem()) {
            $itemGiftvoucher = $itemGiftvoucherRenderer->getItem();
            if ($itemGiftvoucher->getOrderItemId()) {
                $item_ids[] = $itemGiftvoucher->getOrderItemId();
            } else {
                $item_ids[] = $itemGiftvoucher->getId();
            }
        }
        if ($itemDefaultRenderer->getItem()) {
            $itemDefault = $itemDefaultRenderer->getItem();
            if ($itemDefault->getOrderItemId()) {
                $item_ids[] = $itemDefault->getOrderItemId();
            } else {
                $item_ids[] = $itemDefault->getId();
            }
        }
        $lastItem = $itemcollection->getLastItem();
        if ($lastItem->getParentItemId()) {
            $lastId = $lastItem->getParentItemId();
        } else {
            $lastId = $lastItem->getId();
        }

        if (count($item_ids)) {
            if (!in_array($lastId, $item_ids)) {
                return;
            }
        }

        $quoteId = $order->getQuoteId();
        $orderAddress = Mage::getModel('sales/order_address')->getCollection()
                ->addFieldToFilter('parent_id', $order->getId())
                ->addAttributeToSort('entity_id', 'DESC');

        foreach ($orderAddress as $address) {
            $addressCutomer = $address->getData('customer_address_id');
            break;
        }
        $giftwrapCollection = array();
        if ($quoteId) {
            $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId, null, null, $addressCutomer);
        }
        return $giftwrapCollection;
    }

    protected function _beforeToHtml() {
        if (!Mage::helper('magenotification')->checkLicenseKey('Giftwrap')) {
            $this->setTemplate(null);
        }
        return parent::_beforeToHtml();
    }

}