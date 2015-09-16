<?php

class Magestore_Giftwrap_Block_Cart_Minicart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer {

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
        $giftcard = Mage::getModel('giftwrap/card')
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
            if ($giftbox->getMessage()) {
                $options[] = array(
                    'label' => Mage::helper('giftwrap')->__('Gift Message'),
                    'value' => $this->htmlEscape($giftbox->getMessage()),
                );
            }
        }
        return $options;
    }

}