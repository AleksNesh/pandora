<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */   

/**
 * it is an internal method, we don't need name, code, ect for it
 */
class Amasty_Sorting_Model_Method_Qty extends Amasty_Sorting_Model_Method_Abstract
{
    
    public function getCode()
    {
        return 'qty';
    }    
    
    public function getName()
    {
        return 'Quantity';
    }    
    
    // it is an internal method, we don't need name, code, ect for it
    public function apply($collection, $currDir)  
    {
        $select = $collection->getSelect();
        if (strpos($select, 'cataloginventory_stock_status')){
            $select->reset(Zend_Db_Select::ORDER);
        }
        else {
            Mage::getResourceModel('cataloginventory/stock_status')
                ->addStockStatusToSelect($select, Mage::app()->getWebsite());
        }
        $select->order('stock_status.qty ' . $currDir);
               
        return $this;
    }
}