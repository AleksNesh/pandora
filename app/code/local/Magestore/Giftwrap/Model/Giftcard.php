<?php

class Magestore_Giftwrap_Model_Giftcard extends Mage_Core_Model_Abstract {

    protected $_eventPrefix = 'giftwrap_giftcard';
    protected $_eventObject = 'giftcard';

    public function _construct() {
        parent::_construct();
        $this->_init('giftwrap/giftcard');
    }

    public function getStoreGiftcard($giftcard_id, $store_id) {
        $option_id = Mage::getModel('giftwrap/giftcard')->load($giftcard_id)->getOptionId();
        $giftcard = Mage::getModel('giftwrap/giftcard')
                ->getCollection()
                ->addFieldToFilter('option_id', $option_id)
                ->addFieldToFilter('store_id', $store_id)
                ->getFirstItem()
        ;
        return $giftcard;
    }

    public function loadByOptionAndStore($optionId, $storeId) {
        $model = Mage::getModel('giftwrap/giftcard');
        $collection = Mage::getModel('giftwrap/giftcard')->getCollection()
                ->addFieldToFilter('option_id', $optionId)
                ->addFieldToFilter('store_id', $storeId)
        ;
        if (count($collection)) {
            return $model->load($collection->getFirstItem()->getId());
        }
        return $model;
    }

}