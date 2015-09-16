<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */   
class Amasty_Sorting_Model_Method_New extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'created_at';
    }    
    
    public function getName()
    {
        return 'New';
    }
    
    public function apply($collection, $currDir)  
    {
        if (!$this->isEnabled()){
            return $this;
        }
        
        $attr = Mage::getStoreConfig('amsorting/general/new_attr');
        if ($attr) {
            $orders = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
            foreach ($orders as $k => $v){
                if (false !== strpos($v[0], 'created_at')){
                    $orders[$k] = null;
                    unset($orders[$k]);
                }
            }
            $collection->getSelect()->setPart(Zend_Db_Select::ORDER, $orders);
            $collection->addAttributeToSort($attr, $currDir);
        }
        
        return $this;
    }
   
}