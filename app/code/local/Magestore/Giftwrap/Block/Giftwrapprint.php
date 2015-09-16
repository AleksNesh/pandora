<?php

class Magestore_Giftwrap_Block_Giftwrapprint extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        parent::_prepareLayout();
        $action = $this->getRequest()->getActionName();
        if ($action == 'printInvoice') {
            $this->setTemplate('giftwrap/sales/order/invoice/giftwrapprint.phtml');
        } else if ($action == 'printCreditmemo') {
            $this->setTemplate('giftwrap/sales/order/creditmemo/giftwrapprint.phtml');
        } else {
            $this->setTemplate('giftwrap/sales/order/view/giftwrapprint.phtml');
        }
    }

    public function getGiftwrap() {
        if (!$this->hasData('giftwrap')) {
            $this->setData('giftwrap', Mage::registry('giftwrap'));
        }
        return $this->getData('giftwrap');
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
        return Mage::registry('current_order');
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
        $itemId = 0;
        $selections = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
        /* Zend_Debug::dump($selections);die('1');
          $giftwrapItems = array();

          foreach ($this->getOrder()->getAllItems() as $item) {
          $_productId = $item->getProductId();
          if (Mage::helper('giftwrap')->isGiftwrap($_productId)) {
          $giftwrapItems[] = array(
          'id' => $selection->getId(),
          'itemId' => $item->getId(),
          'styleId' => $selection->getStyleId(),
          'giftcardId'	=> $selection->getGiftcardId(),
          'quoteId' => $selection->getQuoteId(),
          'giftwrap_message' => $selection->getMessage()
          );
          }
          } */
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

    public function getOrderItemGiftwrap() {
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        $giftboxCollection = Mage::getModel('giftwrap/selection')
                ->getCollection()
                ->addFieldToFilter('order_id', $order_id);
        $itemcollection = $order->getItemsCollection();
        $is_last = true;
        $lastItem = $itemcollection->getLastItem();
        if ($lastItem->getParentItemId()) {
            $lastId = $lastItem->getParentItemId();
        } else {
            $lastId = $lastItem->getId();
        }
        if ($lastId != $this->getParentBlock()->getItem()->getId()) {
            $is_last = false;
        }
        $giftwrapItems = array();
        $hasGiftwrap = false;
        if (count($giftboxCollection) && $is_last) {
            $hasGiftwrap = true;
        }
        if ($hasGiftwrap) {
            foreach ($giftboxCollection as $selection) {
                $giftwrapItems[] = array(
                    'id' => $selection->getId(),
                    'quantity' => $selection->getQty(),
                    'itemId' => $selection->getItemId(),
                    'styleId' => $selection->getStyleId(),
                    'giftcardId' => $selection->getGiftcardId(),
                    'quoteId' => $selection->getQuoteId(),
                    'character' => $selection->getCharacter(),
                    'giftwrap_message' => $selection->getMessage(),
                    'calculate_by_item' => $selection->getCalculateByItem()
                );
            }
        }
        return $giftwrapItems;
    }

    public function getInvoiceItemGiftwrap() {
        $giftwrapItems = array();
        $invoiceItem = $this->getParentBlock()->getItem();
        $invoice_id = $invoiceItem->getParentId();
        $invoice = Mage::getModel('sales/order_invoice')
                ->load($invoice_id);
        $itemscollection = $invoice->getItemsCollection();
        $lastItem = $itemscollection->getLastItem();
        $hasGiftwrap = true;

        $lastOrderItem = Mage::getModel('sales/order_item')
                ->load($lastItem->getOrderItemId())
        ;
        if ($lastOrderItem->getParentItemId()) {
            $orderItemId = $lastOrderItem->getParentItemId();
        } else {
            $orderItemId = $lastOrderItem->getId();
        }
        $lastId = Mage::getModel('sales/order_invoice_item')
                ->getCollection()
                ->addFieldToFilter('parent_id', $invoice_id)
                ->addFieldToFilter('order_item_id', $orderItemId)
                ->getFirstItem()
                ->getId();

        if ($lastId != $invoiceItem->getId()) {
            $hasGiftwrap = false;
        }
        if ($invoice_id) {
            $giftboxCollection = Mage::getModel('giftwrap/selection')
                    ->getCollection()
                    ->addFieldToFilter('invoice_id', $invoice_id);
        }
        if ($hasGiftwrap && count($giftboxCollection) > 0) {
            foreach ($giftboxCollection as $selection) {
                $giftwrapItems[] = array(
                    'id' => $selection->getId(),
                    'quantity' => $selection->getQty(),
                    'itemId' => $selection->getItemId(),
                    'styleId' => $selection->getStyleId(),
                    'giftcardId' => $selection->getGiftcardId(),
                    'quoteId' => $selection->getQuoteId(),
                    'character' => $selection->getCharacter(),
                    'giftwrap_message' => $selection->getMessage(),
                    'calculate_by_item' => $selection->getCalculateByItem()
                );
            }
        }
        return $giftwrapItems;
    }

    public function getCreditmemoItemGiftwrap() {
        $giftwrapItems = array();
        $creditmemoItem = $this->getParentBlock()->getItem();
        $creditmemo_id = $creditmemoItem->getParentId();
        $creditmemo = Mage::getModel('sales/order_creditmemo')
                ->load($creditmemo_id);
        $itemscollection = $creditmemo->getItemsCollection();
        $lastItem = $itemscollection->getLastItem();
        $hasGiftwrap = true;

        $lastOrderItem = Mage::getModel('sales/order_item')
                ->load($lastItem->getOrderItemId())
        ;
        if ($lastOrderItem->getParentItemId()) {
            $orderItemId = $lastOrderItem->getParentItemId();
        } else {
            $orderItemId = $lastOrderItem->getId();
        }
        $lastId = Mage::getModel('sales/order_creditmemo_item')
                ->getCollection()
                ->addFieldToFilter('parent_id', $creditmemo_id)
                ->addFieldToFilter('order_item_id', $orderItemId)
                ->getFirstItem()
                ->getId();
        if ($lastId != $creditmemoItem->getId()) {
            $hasGiftwrap = false;
        }
        if ($creditmemo_id) {
            $giftboxCollection = Mage::getModel('giftwrap/selection')
                    ->getCollection()
                    ->addFieldToFilter('creditmemo_id', $creditmemo_id);
        }
        if ($hasGiftwrap && count($giftboxCollection) > 0) {
            foreach ($giftboxCollection as $selection) {
                $giftwrapItems[] = array(
                    'id' => $selection->getId(),
                    'quantity' => $selection->getQty(),
                    'itemId' => $selection->getItemId(),
                    'styleId' => $selection->getStyleId(),
                    'giftcardId' => $selection->getGiftcardId(),
                    'quoteId' => $selection->getQuoteId(),
                    'character' => $selection->getCharacter(),
                    'giftwrap_message' => $selection->getMessage(),
                    'calculate_by_item' => $selection->getCalculateByItem()
                );
            }
        }
        return $giftwrapItems;
    }

}