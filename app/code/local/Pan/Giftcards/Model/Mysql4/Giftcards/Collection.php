<?php

class Pan_Giftcards_Model_Mysql4_Giftcards_Collection extends Webtex_Giftcards_Model_Mysql4_Giftcards_Collection
{
    protected function _initSelect()
    {
    	parent::_initSelect();

    	$sales_flat_order_table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $this->getSelect()->joinLeft($sales_flat_order_table , 'main_table.order_id = '.$sales_flat_order_table.'.entity_id', 'increment_id');

    	return $this;
    }
}