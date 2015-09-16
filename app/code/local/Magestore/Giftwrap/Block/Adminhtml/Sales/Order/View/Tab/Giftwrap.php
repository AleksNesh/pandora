<?php

class Magestore_Giftwrap_Block_Adminhtml_Sales_Order_View_Tab_Giftwrap extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function _construct() {
        parent::_construct();
        $this->setTemplate('giftwrap/sales/order/view/tab/giftwrap.phtml');
    }

    public function getTabLabel() {
        return Mage::helper('giftwrap')->__('Gift Wrap Information');
    }

    public function getTabTitle() {
        return Mage::helper('sales')->__('Gift Wrap Information');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function getOrderItemGiftwrap($orderId = null) {
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        //$invoice_id = Mage::app()->getRequest()->getParam('invoice_id');
        if (!$orderId) {
            $order = $this->getOrder();
        } else {
            $order = Mage::getModel('sales/order')->load($orderId);
        }
        $quoteId = $order->getQuoteId();
        $orderAddress = Mage::getModel('sales/order_address')->getCollection()
                ->addFieldToFilter('parent_id', $order->getId())
                ->addAttributeToSort('entity_id', 'DESC');
        ;
        foreach ($orderAddress as $address) {
            $addressCutomer = $address->getData('customer_address_id');
            break;
        }
        $giftwrapCollection = array();
        if ($quoteId) {
            $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId, null, null, $addressCutomer);
            /* if (count($giftwrapCollection) == 1 && $giftwrapCollection[0]['itemId'] == 0) {
              return $this->getAllGiftwrapItemInCart();
              } */
        }

        return $giftwrapCollection;
    }

    public function getInvoiceItemGiftwrap() {
        $invoice_id = Mage::app()->getRequest()->getParam('invoice_id');
        $invoiceItems = Mage::getModel('sales/order_invoice_item')->getCollection()
                ->addFieldToFilter('parent_id', $invoice_id);
        $giftwrapCollection = array();
        foreach ($invoiceItems as $item) {
            $orderItemId = $item->getOrderItemId();
            $quoteItemId = Mage::getModel('sales/order_item')->getCollection()
                            ->addFieldToFilter('item_id', $orderItemId)->getFirstItem()->getQuoteItemId();
            $selectionId = Mage::getModel('giftwrap/selectionitem')->getCollection()
                            ->addFieldToFilter('item_id', $quoteItemId)->getFirstItem()->getSelectionId();
            if(Mage::getModel('giftwrap/selection')->load($selectionId)->getInvoiceId() == $invoice_id){
                $giftwrapCollection[] = Mage::getModel('giftwrap/selection')->load($selectionId);
            }
        }

        return $giftwrapCollection;
    }

    public function getCreditmemoItemGiftwrap() {
        $creditmemo_id = Mage::app()->getRequest()->getParam('creditmemo_id');
        $creditmemoItems = Mage::getModel('sales/order_creditmemo_item')->getCollection()
                ->addFieldToFilter('parent_id', $creditmemo_id);
        $giftwrapCollection = array();
        foreach ($creditmemoItems as $item) {
            $orderItemId = $item->getOrderItemId();
            $quoteItemId = Mage::getModel('sales/order_item')->getCollection()
                            ->addFieldToFilter('item_id', $orderItemId)->getFirstItem()->getQuoteItemId();
            $selectionId = Mage::getModel('giftwrap/selectionitem')->getCollection()
                            ->addFieldToFilter('item_id', $quoteItemId)->getFirstItem()->getSelectionId();
            if(Mage::getModel('giftwrap/selection')->load($selectionId)->getCreditmemoId() == $creditmemo_id){
                $giftwrapCollection[] = Mage::getModel('giftwrap/selection')->load($selectionId);
            }
        }

        return $giftwrapCollection;
    }

    public function isGiftwrapAll() {
        $quoteId = $this->getOrder()->getQuoteId();
        $giftwrapCollection = array();
        if ($quoteId) {
            $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
            if (count($giftwrapCollection) == 1 && $giftwrapCollection[0]['itemId'] == 0) {
                return true;
            }
        }
        return false;
    }

    public function getAllGiftwrapItemInCart($quoteId) {
        //$quoteId = $this->getOrder()->getQuoteId();
        $selections = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
        return $selections;
    }

    public function getProduct($productId) {
        return Mage::getModel('catalog/product')->load($productId);
    }

    public function getGiftwrapStyleName($styleId) {
        return $this->getStyle($styleId)->getTitle();
    }

    public function getGiftcardName($giftcardId) {
        return $this->getGiftcard($giftcardId)->getName();
    }

    public function getGiftwrapStyleImage($styleId) {
        return $this->getStyle($styleId)->getImage();
    }

    public function getGiftcardImage($giftcardId) {
        return $this->getGiftcard($giftcardId)->getImage();
    }

    public function getStyle($styleId) {
        return Mage::getModel('giftwrap/giftwrap')->load($styleId);
    }

    public function getGiftcard($giftcardId) {
        return Mage::getModel('giftwrap/giftcard')->load($giftcardId);
    }

}