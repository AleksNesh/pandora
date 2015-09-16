<?php

class Magestore_Giftwrap_Block_Catalog_Product_Giftwrap_Existing extends Mage_Core_Block_Template {

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
        $config = Mage::getStoreConfig('giftwrap/style/giftwrap_view_type');
        if ($config == 'radio') {
            $this->setTemplate('giftwrap/catalog/product/view/existing/type/radio.phtml');
        } else {
            $this->setTemplate('giftwrap/catalog/product/view/existing/type/select.phtml');
        }
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

    public function getGiftboxCollection() {
        $storeId = Mage::app()->getStore()->getId();
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $collection = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('quote_id', $quote->getId())
        ;
        $selection_ids = array();
        if (count($collection) && !Mage::helper('customer')->isLoggedIn()) {
            $cache = array();
            $quoteItems = array();
            $items = $quote->getItemsCollection();
            if (!$items->getSize()) {
                foreach ($collection as $value) {
                    $value->delete();
                }
            } else {
                $giftwrapItemCollection = Mage::getModel('giftwrap/selectionitem')->getCollection();
                foreach ($items as $item) {
                    $quoteItems[] = $item->getId();
                    $productId = $item->getProduct()->getId();
                    $cache[$item->getId()] = $productId;
                }
                foreach ($collection as $value) {
                    $giftwrapItemCollection->addFieldToFilter('selection_id', $value->getId());
                    $giftwrapItemCollection->addFieldToFilter('item_id', array('in' => $quoteItems));
                    if (count($giftwrapItemCollection) > 0) {
                        $selection_ids[] = $value->getId();
                    } else {
                        $value->delete();
                    }
                }
                if (count($selection_ids) > 0) {
                    $newcollection = Mage::getModel('giftwrap/selection')->getCollection()
                            ->addFieldToFilter('id', array('in' => $selection_ids));
                }
            }
            $object = new Varien_Object();
            $object->setData('item_list', $cache);
            Mage::getModel('core/session')->setData('cache', $cache);
            Mage::getModel('core/session')->setData('quote_giftwrap', $quote->getId());
        }
        if ($newcollection) {
            return $newcollection;
        } else {
            return $collection;
        }
    }

    protected function _beforeToHtml() {
        if (!Mage::helper('magenotification')->checkLicenseKey('Giftwrap')) {
            $this->setTemplate(null);
        }
        return parent::_beforeToHtml();
    }

}