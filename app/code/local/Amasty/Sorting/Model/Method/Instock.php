<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */   

/**
 * it is an internal method, we don't need name, code, ect for it
 */
class Amasty_Sorting_Model_Method_Instock extends Amasty_Sorting_Model_Method_Abstract
{
    // it is an internal method, we don't need name, code, ect for it
    public function apply($collection, $currDir)  
    {
        $show = Mage::getStoreConfig('amsorting/general/out_of_stock_last');
        if (!$show)
            return $this;
            
        //skip search results    
        $isSearch = in_array(Mage::app()->getRequest()->getModuleName(), array('sqli_singlesearchresult', 'catalogsearch')); 
        if ($isSearch && 2 == $show)
            return $this;
        
        $select = $collection->getSelect();
        
        if (!strpos($select->__toString(), 'cataloginventory_stock_status')){
            Mage::getResourceModel('cataloginventory/stock_status')
                ->addStockStatusToSelect($select, Mage::app()->getWebsite());
        }
        
        $field = 'salable desc';
        if (Mage::getStoreConfig('amsorting/general/out_of_stock_qty')){
            $field = new Zend_Db_Expr('IF(stock_status.qty > 0, 0, 1)');
        }
        $select->order($field);
        
        // move to the first position
        $orders = $select->getPart(Zend_Db_Select::ORDER);
        if (count($orders) > 1){
            $last = array_pop($orders);
            array_unshift($orders, $last);
            $select->setPart(Zend_Db_Select::ORDER, $orders); 
        }            
               
        return $this;
    }
}