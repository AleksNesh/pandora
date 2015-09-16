<?php

class MageWorx_Tweaks_Model_Adminhtml_System_Config_Source_Catalog_ListSort
extends Mage_Adminhtml_Model_System_Config_Source_Catalog_ListSort {

    public function toOptionArray() {
        $options = array();
        $options[] = array(
                'label' => Mage::helper('catalog')->__('Best Value'),
                'value' => 'position'
        );

        if (Mage::helper('tweaks')->isOrderByBestsellersEnabled()) {
            $options[] = array(
                    'label' => Mage::helper('catalog')->__('Bestsellers'),
                    'value'	=> 'bestsellers');
        }
        if (Mage::helper('tweaks')->isOrderByNewestProductEnabled()) {
            $options[] = array(
                    'label' => Mage::helper('catalog')->__('Newest'),
                    'value'	=> 'newest');
        }
//        if (Mage::helper('tweaks')->isOrderByPriceUpEnabled()) {
//          $options[] = array(
//                  'label' => Mage::helper('catalog')->__('Price:Low to High'),
//                  'value'	=> 'price_up');
//      }
//      if (Mage::helper('tweaks')->isOrderByPriceDownEnabled()) {
//          $options[] = array(
//                  'label' => Mage::helper('catalog')->__('Price:High to Low'),
//                  'value'	=> 'price_down');
//      }
        if (Mage::helper('tweaks')->isOrderByAverageCustomerReviewEnabled()) {
            $options[] = array(
                    'label' => Mage::helper('catalog')->__('Average Customer Review'),
                    'value'	=> 'reviews');
        }

        foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
            $options[] = array(
                    'label' => Mage::helper('catalog')->__($attribute['frontend_label']),
                    'value' => $attribute['attribute_code']
            );
        }
        return $options;
    }

}
