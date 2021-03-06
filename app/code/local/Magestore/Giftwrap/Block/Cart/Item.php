<?php

class Magestore_Giftwrap_Block_Cart_Item extends Mage_Checkout_Block_Cart_Item_Renderer {

    public function getProductOptions() {
        
        $options = parent::getProductOptions();
        $item = $this->getItem();
        $giftwrapItem = Mage::getModel('giftwrap/selectionitem')
                ->getCollection()
                ->addFieldToFilter('item_id', $item->getId())
                ->getFirstItem();
        $giftbox = Mage::getModel('giftwrap/selection')
                ->load($giftwrapItem->getSelectionId());
        $giftwrap = Mage::getModel('giftwrap/giftwrap')
                ->load($giftbox->getStyleId());
        $giftcard = Mage::getModel('giftwrap/giftcard')
                ->load($giftbox->getGiftcardId());
        if ($giftwrapItem->getId()) {
            $options[] = array(
                'label' => Mage::helper('giftwrap')->__('Gift Wrap'),
                'value' => $this->htmlEscape($giftwrap->getTitle()),
            );
            if ($giftcard->getId()) {
                $options[] = array(
                    'label' => Mage::helper('giftwrap')->__('Gift Card'),
                    'value' => $this->htmlEscape($giftcard->getName()),
                );
            }
            if ($giftcard->getId() && $giftbox->getMessage()) {
                $options[] = array(
                    'label' => Mage::helper('giftwrap')->__('Gift Message'),
                    'value' => $this->htmlEscape($giftbox->getMessage()),
                );
            }
            
        }
        return $options;
    }

}

