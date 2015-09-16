<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */  
class Amasty_Sorting_Block_Featured extends Mage_Catalog_Block_Product_Abstract
{
    public function getCollection()
    {
        $layer      = Mage::getSingleton('catalog/layer');
        $categoryId = $this->getData('category');
        
        // Store Current Category
        $currentCategory = $layer->getCurrentCategory();
        
        if ($categoryId) {
        	$category = Mage::getModel('catalog/category')->load($categoryId);
        	if ($category) { 
        		$layer->setCurrentCategory($category);
        	}
        }
        
        $collection = $layer->getCurrentCategory()->getProductCollection();        
        $layer->prepareProductCollection($collection);        
        
        $collection->addStoreFilter();
        
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection); 
        
        if ($this->getSorting()){
            $method = Mage::getModel('amsorting/method_' . $this->getSorting()); 
            if (!$method){
                $msg = "Please provide one of the following sorting methods:";
                foreach (Mage::helper('amsorting')->getMethods() as $className){
                    $msg .= "$className; ";
                }
                echo $msg;
                return array();
            }

            // it's special method ut it uses default attribute.
            if ('new' == $this->getSorting() && !Mage::getStoreConfig('amsorting/general/new_attr')){ 
                $collection->addAttributeToSort('created_at','desc');
            }
            else {
                $old = $method->isEnabled();
                $method->setEnabled(true);
                $method->apply($collection, 'desc');
                $method->setEnabled($old);
            }
        }
        elseif($this->getDefSorting()){
            $collection->addAttributeToSort($this->getDefSorting(), $this->getDefDirection() == 'desc' ? 'desc' : 'asc'); 
        }
        else {
            echo 'Please use param `sorting` or `def_sorting`';
            return array();            
        }
        
        $collection->setPage(1, $this->getLimit());
        
        // Restore Current Category
        $layer->setCurrentCategory($currentCategory);
        
        return $collection;
    }
    
}