<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */   
class Amasty_Sorting_Model_Method_Bestselling extends Amasty_Sorting_Model_Method_Abstract
{
    protected $isAllGrouped = false;
    
    public function getCode()
    {
        return 'bestsellers';
    }    
    
    public function getName()
    {
        return 'Best Sellers';
    }
    
    public function getIndexTable()
    {
        return 'amsorting/' . $this->getCode();
    } 
    
    public function getColumnSelect()
    {
        $sql =' SELECT SUM(order_item.qty_ordered)'
            . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('sales/order_item') 
            . ' AS order_item';
            
        if ($this->isAllGrouped){  
            $sql .= ' INNER JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/quote_item_option') 
                 . ' AS order_item_option'
                 .  '   ON (order_item.item_id = order_item_option.item_id AND order_item_option.code="product_type")'
                 .  ' WHERE order_item_option.product_id = e.entity_id ' 
            ;
        }  
        else {
            $sql .= ' WHERE order_item.product_id = e.entity_id ';
        }
             
        $sql .= $this->getPeriodCondition('order_item.created_at', 'best_period'); 
        $sql .= $this->getStoreCondition('order_item.store_id'); 
        
        
        return new Zend_Db_Expr('(' . $sql . ')');         
    }    
     
    public function getIndexSelect() 
    {
        $sql =' SELECT product_id, store_id, SUM(qty_ordered)'
            . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('sales/order_item') . ' AS order_item'
            . ' WHERE 1 ' 
            . $this->getPeriodCondition('order_item.created_at', 'best_period') 
            . ' GROUP BY product_id, store_id'
        ;
        if ($this->isAllGrouped){    
            $sql =' SELECT order_item_option.product_id, order_item.store_id, SUM(order_item.qty_ordered)'
                 .  ' FROM ' . Mage::getSingleton('core/resource')->getTableName('sales/order_item') . ' AS order_item'
                 .  ' INNER JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/quote_item_option') 
                 .  ' AS order_item_option'
                 .  '   ON (order_item.item_id = order_item_option.item_id AND order_item_option.code="product_type")'
                 .  ' WHERE 1 ' 
                 . $this->getPeriodCondition('order_item.created_at', 'best_period') 
                 . ' GROUP BY order_item_option.product_id, order_item.store_id'
            ;
            
        }      
        
        return $sql;
    }   
    

    public function apply($collection, $currDir)  
    {
        $attr = Mage::getStoreConfig('amsorting/general/best_attr');
        if ($attr) {
            $collection->addAttributeToSort($attr, $currDir);
        }
        return parent::apply($collection, $currDir);
    }
   
}