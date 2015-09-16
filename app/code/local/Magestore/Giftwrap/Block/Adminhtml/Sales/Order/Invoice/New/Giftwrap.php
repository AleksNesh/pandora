<?php

class Magestore_Giftwrap_Block_Adminhtml_Sales_Order_Invoice_New_Giftwrap extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function _construct() {
        parent::_construct();
        $this->setTemplate('giftwrap/sales/order/invoice/new/giftwrap.phtml');
    }

    public function getTabLabel() {
        return Mage::helper('giftwrap')->__('Giftwrap Information');
    }

    public function getTabTitle() {
        return Mage::helper('sales')->__('Giftwrap Information');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder() {
        if (Mage::registry('current_order'))
            return Mage::registry('current_order');
        else {
            $order_id = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($order_id);
            return $order;
        }
    }

    public function getOrderItemGiftwrap($orderId = null) {
        if (!$orderId) {
            $order_id = Mage::app()->getRequest()->getParam('order_id');
        } else {
            $order_id = $orderId;
        }
        $invoice_id = Mage::app()->getRequest()->getParam('invoice_id');
        $shipment_id = Mage::app()->getRequest()->getParam('shipment_id');
        $creditmemo_id = Mage::app()->getRequest()->getParam('creditmemo_id');
        if ($order_id) {
            $order = Mage::getModel('sales/order')->load($order_id);
        } else if ($invoice_id) {
            $order = Mage::getModel('sales/order_invoice')->load($invoice_id)->getOrder();
        } else if ($shipment_id) {
            $order = Mage::getModel('sales/order_shipment')->load($shipment_id)->getOrder();
        } else if ($creditmemo_id) {
            $order = Mage::getModel('sales/order_creditmemo')->load($creditmemo_id)->getOrder();
        }
        $itemcollection = $order->getItemsCollection();
        foreach ($itemcollection as $itemcol){
          if($itemcol->getQtyOrdered() != $itemcol->getQtyInvoiced() && $itemcol->getBasePrice()!=0){
              $lastId = $itemcol->getId();
          }
        }
        if ($lastId != $this->getParentBlock()->getItem()->getId()) {
            return;
        }
        if (!$order->getId()) {
            $order = $this->getOrder();
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
        //Zend_Debug::dump($quoteId);die();
        $giftwrapCollection = array();
        if ($quoteId) {
            $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId, null, null, $addressCutomer);
            /* if (count($giftwrapCollection) == 1 && $giftwrapCollection[0]['itemId'] == 0) {
              return $this->getAllGiftwrapItemInCart();
              } */
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

    public function getAllGiftwrapItemInCart() {
        $quoteId = $this->getOrder()->getQuoteId();
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
