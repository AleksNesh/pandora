<?php

class Magestore_Giftwrap_Model_Selection extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('giftwrap/selection');
    }

    /*
     * 	$quoteId : int
     * 	$itemId : int
     * 	Return Collection : Magestore_Giftwrap_Model_Selection
     */

    public function loadByQuoteId($quoteId, $itemId) {
        $this->_getResource()->loadByQuoteId($this, $quoteId, $itemId);
        $this->_afterLoad();
        return $this;
    }

    /*
     * 	$quoteId : int
     * 	Return array : all field of Object giftwrap/selection
     */

    public function getSelectionByQuoteId($quoteId, $storeId = null, $address_id = null, $address_customer_id = null) {
        if ($address_id) {
            $collection = $this->getCollection()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('addressgift_id', $address_id)
            ;
            
            if (!count($collection)):
                if (Mage::getSingleton('core/session')->getData('multi') != 1):
                    $collection = $this->getCollection()
                            ->addFieldToFilter('quote_id', $quoteId)
                    ;
                endif;
            endif;
        }else {
            if ($address_customer_id) {
                $collection = $this->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId)
                        ->addFieldToFilter('addresscustomer_id', $address_customer_id)
                ;
                if (!count($collection)):
                    if (Mage::getSingleton('core/session')->getData('multi') != 1):
                        $collection = $this->getCollection()
                                ->addFieldToFilter('quote_id', $quoteId)
                        ;
                    endif;
                endif;
            }else {
                $collection = $this->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId)
                ;
            }
        }

        if ($storeId)
            $collection->getSelect()
                    ->joinRight(array('style' => 'giftwrap'), 'main_table.style_id=style.giftwrap_id AND style.store_id =' . $storeId)
            ;
        $items = array();
        foreach ($collection as $value) {
            $items[] = array(
                'id' => $value->getId(),
                'quantity' => $value->getQty(),
                'itemId' => $value->getItemId(),
                'styleId' => $value->getStyleId(),
                'giftcardId' => $value->getGiftcardId(),
                'quoteId' => $value->getQuoteId(),
                'character' => $value->getCharacter(),
                'giftwrap_message' => $value->getMessage()
            );
        }
        return $items;
    }

    public function deleteSelectionByQuoteId($quoteId, $itemId = null) {
        $collection = $this->getCollection();
        $items = array();
        foreach ($collection as $selection) {
            if ($selection->getQuoteId() == $quoteId) {
                if ($itemId) {
                    $ids = explode(',', $selection->getItemId());
                    $newItemIds = '';
                    if (in_array($itemId, $ids)) {
                        if (count($ids) == 1)
                            $selection->delete();
                        else {
                            foreach ($ids as $id) {
                                if ($id != $itemId)
                                    $newItemIds.=$id . ',';
                            }
                            $newItemIds = trim($newItemIds, ',');
                            $selection->setItemId($newItemIds)
                                    ->save();
                        }
                    }
                }else {
                    $selection->delete();
                }
            }
        }
    }

    public function deleteSelection($deleteAll = false) {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $collection = $this->getCollection()
                ->addFieldToFilter("quote_id", $quoteId);

        foreach ($collection as $selection) {
            if ($selection->getItemId() == 0) {
                $selection->delete();
                $isWrapAll = true;
            }
        }

        if ($deleteAll) {
            foreach ($collection as $selection) {
                $selection->delete();
            }
        }
    }

    /*
     * 	Return array : all field of Object giftwrap/selection
     */

    public function getSelection() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $allItems = Mage::getModel('giftwrap/selection')->getCollection();
        $items = array();
        if ($allItems) {
            foreach ($allItems as $value) {
                if ($value->getQuoteId() == $quoteId) {
                    $items[] = array(
                        'itemId' => $value->getItemId(),
                        'styleId' => $value->getStyleId(),
                        'quoteId' => $value->getQuoteId(),
                        'character' => $value->getCharacter(),
                        'giftwrap_message' => $value->getMessage()
                    );
                }
            }
        }
        return $items;
    }

    public function removeSelection($quoteId) {
        $this->_getResource()->removeAllSelection($quoteId);
    }

    public function getItemCollection() {
        if ($this->getId()) {
            $itemcollection = Mage::getModel('giftwrap/selectionitem')
                    ->getCollection()
                    ->addFieldToFilter('selection_id', $this->getId())
            ;
            return $itemcollection;
        }
        return null;
    }

    public function deleteAllItems() {
        $itemCollection = $this->getItemCollection();
        foreach ($itemCollection as $selectionItem) {
            $selectionItem->delete();
        }
    }

}