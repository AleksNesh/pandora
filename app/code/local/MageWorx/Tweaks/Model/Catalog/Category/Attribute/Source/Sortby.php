<?php
class MageWorx_Tweaks_Model_Catalog_Category_Attribute_Source_Sortby 
extends Mage_Catalog_Model_Category_Attribute_Source_Sortby {

    public function getAllOptions() {
        if (is_null($this->_options)) {
            $this->_options = array(array(
                            'label' => Mage::helper('catalog')->__('Best Value'),
                            'value' => 'position'));

            if (Mage::helper('tweaks')->isOrderByBestsellersEnabled()) {
                $this->_options[] = array(
                        'label' => Mage::helper('catalog')->__('Bestsellers'),
                        'value'	=> 'bestsellers');
            }
            if (Mage::helper('tweaks')->isOrderByNewestProductEnabled()) {
                $this->_options[] = array(
                        'label' => Mage::helper('catalog')->__('Newest'),
                        'value'	=> 'newest');
            }
//            if (Mage::helper('tweaks')->isOrderByPriceUpEnabled()) {
//              $this->_options[] = array(
//                      'label' => Mage::helper('catalog')->__('Price:Low to High'),
//                      'value'	=> 'price_up');
//          }
//          if (Mage::helper('tweaks')->isOrderByPriceDownEnabled()) {
//              $this->_options[] = array(
//                      'label' => Mage::helper('catalog')->__('Price:High to Low'),
//                      'value'	=> 'price_down');
//          }
            if (Mage::helper('tweaks')->isOrderByAverageCustomerReviewEnabled()) {
                $this->_options[] = array(
                        'label' => Mage::helper('catalog')->__('Average Customer Review'),
                        'value'	=> 'reviews');
            }

            foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
                $this->_options[] = array(
                        'label' => Mage::helper('catalog')->__($attribute['frontend_label']),
                        'value' => $attribute['attribute_code']
                );
            }
        }
        return $this->_options;
    }
}
