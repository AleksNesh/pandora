<?php

class MageWorx_Tweaks_Model_Catalog_Config extends Mage_Catalog_Model_Config {
    public function getAttributeUsedForSortByArray() {
        $options = array(
                'position'  => Mage::helper('catalog')->__('Position'),
        );

        if (Mage::helper('tweaks')->isOrderByBestsellersEnabled()) {
            $options['bestsellers'] = Mage::helper('catalog')->__('Bestsellers');
        }
        if (Mage::helper('tweaks')->isOrderByNewestProductEnabled()) {
            $options['newest'] = Mage::helper('catalog')->__('Newest');
        }
//        if (Mage::helper('tweaks')->isOrderByPriceUpEnabled()) {
//          $options['price_up'] = Mage::helper('catalog')->__('Price:Low to High');
//      }
//      if (Mage::helper('tweaks')->isOrderByPriceDownEnabled()) {
//          $options['price_down'] = Mage::helper('catalog')->__('Price:High to Low');
//      }
        if (Mage::helper('tweaks')->isOrderByAverageCustomerReviewEnabled()) {
            $options['review'] = Mage::helper('catalog')->__('Average Customer Review');
        }

        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            $options[$attribute->getAttributeCode()] = Mage::helper('catalog')->__($attribute->getFrontendLabel());
        }

        return $options;
    }

}
