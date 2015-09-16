<?php

class Magestore_Giftwrap_Block_Giftbox_Paper extends Mage_Checkout_Block_Cart_Item_Renderer {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

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

    public function getWrapedItems() {
        $items = Mage::helper('giftwrap')->getAllItems();
        $item_ids = array();
        foreach ($items as $it) {
            $its = explode(',', $it['itemId']);
            foreach ($its as $i) {
                $item_ids[] = $i;
            }
        }
        return $item_ids;
    }

    public function getItemsCollection() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $items = $quote->getItemsCollection();
        return $items;
    }

    public function getNotAvailableItems($giftbox_id) {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $giftboxs = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
                ->addFieldToFilter('id', array('neq' => $giftbox_id));
        $item_ids = array();
        foreach ($giftboxs as $giftbox) {
            $its = explode(',', $giftbox->getItemId());
            foreach ($its as $i) {
                $item_ids[] = $i;
            }
        }
        return $item_ids;
    }

    public function getAvailableItems($id = null) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $giftwrapItems = array();
        foreach ($quote->getAllVisibleItems() as $item) {
            if (Mage::helper('giftwrap')->isGiftwrap(
                            $item->getProduct()
                                    ->getId())) {
                if ($this->getNumberOfItemsCanWraped($item, $id) > 0)
                    $giftwrapItems[] = $item->getId();
            }
        }
        return $giftwrapItems;
    }

    public function getItemIdsByGiftbox($giftbox_id) {
        $item_ids = array();
        $giftbox = Mage::getModel('giftwrap/selection')->load($giftbox_id);
        $its = $giftbox->getItemCollection();
        foreach ($its as $it) {
            $item_ids[] = $it->getItemId();
        }
        return $item_ids;
    }

    public function getNumberOfItemsCanWraped($item, $selection_id = null) {
        $quoteId = $item->getQuoteId();

        $qty = floatval($item->getQty());
        $selectionCollection = Mage::getModel('giftwrap/selection')
                ->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
        ;
        foreach ($selectionCollection as $selection) {
            $selectionItemCollection = Mage::getModel('giftwrap/selectionitem')
                    ->getCollection()
                    ->addFieldToFilter('selection_id', $selection->getId())
                    ->addFieldToFilter('item_id', $item->getId())
            ;
            foreach ($selectionItemCollection as $selectionItem) {
                $qty = floatval($qty) - floatval($selection->getQty()) * floatval($selectionItem->getQty());
            }
        }
        if ($selection_id) {
            $selection = Mage::getModel('giftwrap/selection')->load($selection_id);
            $selectionItem = Mage::getModel('giftwrap/selectionitem')->loadBySelectionAndItem($selection_id, $item
                            ->getId());
            return $qty + floatval($selectionItem->getQty()) * floatval($selection->getQty());
        }
        return $qty;
    }

    public function getAllGiftcards() {
        $gifcards = Mage::getModel('giftwrap/giftcard')->getCollection()
                ->addFieldToFilter(
                        'store_id', Mage::app()->getStore()
                        ->getId())
                ->addFieldToFilter('status', 1);
        return $gifcards;
    }

    public function getCurrentItems($id = null, $item = null) {
        $itemModel = Mage::getModel('sales/quote_item')->load($item);
        $giftwrapItems = array();
        if ($this->getNumberOfItemsCanWraped($itemModel, $id) > 0)
            $giftwrapItems[] = $item;

        return $giftwrapItems;
    }

    public function getNumberAlreadyWraped($item, $selection_id = null) {
        $quoteId = $item->getQuoteId();

        $qty = floatval($item->getQty());
        $selectionCollection = Mage::getModel('giftwrap/selection')
                ->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
        ;
        foreach ($selectionCollection as $selection) {
            $selectionItemCollection = Mage::getModel('giftwrap/selectionitem')
                    ->getCollection()
                    ->addFieldToFilter('selection_id', $selection->getId())
                    ->addFieldToFilter('item_id', $item->getId())
            ;
            foreach ($selectionItemCollection as $selectionItem) {
                $qty = floatval($qty) - floatval($selection->getQty()) * floatval($selectionItem->getQty());
            }
        }
        return $qty;
    }

    public function getNumberCanWraped($item, $selection_id) {
        $qty = $this->getNumberAlreadyWraped($item, $selection_id);
        $gifwrapItemId = $this->getRequest()->getParam('item_id');
        if ($gifwrapItemId)
            if ($item->getId() == $gifwrapItemId)
                $qty = floatval($qty) - 1;
        return $qty;
    }

    public function getQtyWrap($item) {
        $gifwrapItemId = $this->getRequest()->getParam('item_id');
        if ($gifwrapItemId && $item->getId() == $gifwrapItemId)
            return $item->getQty();
        return $item->getQty() - $this->getNumberAlreadyWraped($item);
    }

}