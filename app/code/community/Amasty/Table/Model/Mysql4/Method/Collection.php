<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Model_Mysql4_Method_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amtable/method');
    }
    
    public function addStoreFilter($storeId)
    {
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,'.$storeId.',%"');
        
        return $this;
    }    
    
    public function addCustomerGroupFilter($groupId)
    {
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,'.$groupId.',%"');
        
        return $this;
    }    
}