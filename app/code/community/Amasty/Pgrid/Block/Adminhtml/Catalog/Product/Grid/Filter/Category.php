<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Filter_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getCondition()
    {
        
        $collection = Mage::registry('product_collection');
        
        if ($collection)
        {
            if ($this->getValue())
            {
                $collection->addCategoryFilter(Mage::getModel('catalog/category')->load($this->getValue()));
            }
        
            if (0 == $this->getValue() && strlen($this->getValue()) > 0)
            {
                
                $collection->getSelect()->joinLeft(array('nocat_idx' => $collection->getTable('catalog/category_product_index')),
                    '(nocat_idx.product_id = e.entity_id AND nocat_idx.position != 0)',
                    array(
                        'nocat_idx.category_id',
                    )
                );
                $collection->getSelect()->where('nocat_idx.category_id IS NULL');
            }
        }
        return null;
    }
}