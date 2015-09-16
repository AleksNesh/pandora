<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */   
class Amasty_Sorting_Model_Method_Saving extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'saving';
    }    
    
    public function getName()
    {
        return 'Biggest Saving';
    }
    
    public function apply($collection, $currDir)  
    {
        if (!$this->isEnabled()){
            return $this;
        }

        $alias = 'price_index';
        if (preg_match('/`([a-z0-9\_]+)`\.`price`/', $collection->getSelect()->__toString(), $m)){
		$alias = $m[1];
        }

        $collection->getSelect()->order("(`$alias`.price - `minimal_price`) $currDir");
        
        return $this;
    }
   
}