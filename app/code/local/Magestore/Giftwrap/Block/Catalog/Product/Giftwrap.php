<?php

class Magestore_Giftwrap_Block_Catalog_Product_Giftwrap extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
    }

    public function getGiftwrapCollection() {
        $papers = Mage::getModel('giftwrap/giftwrap')
                ->getCollection()
                ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
                ->addFieldToFilter('status', 1);
        return $papers;
    }
    
    public function getAllGiftcards() {
        $gifcards = Mage::getModel('giftwrap/giftcard')->getCollection()
                ->addFieldToFilter(
                        'store_id', Mage::app()->getStore()
                        ->getId())
                ->addFieldToFilter('status', 1);
        return $gifcards;
    }
    
    public function _prepareLayout() {
        $this->setTemplate('giftwrap/catalog/product/view/giftwrap.phtml');
    }

    public function getProduct() {
        return Mage::registry('product');
    }

    public function getGiftWrapToolTip() {
        return Mage::getStoreConfig('giftwrap/message/product_giftwrap');
    }

    public function getNoGiftWrapToolTip() {
        return Mage::getStoreConfig('giftwrap/message/product_no_giftwrap');
    }

    protected function _beforeToHtml() {
        if (!Mage::helper('magenotification')->checkLicenseKey('Giftwrap')) {
            $this->setTemplate(null);
        }
        return parent::_beforeToHtml();
    }

}