<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Filter_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getCondition()
    {
        if ('null' == $this->getValue())
        {
            return array('null' => true);
        }
        return array('finset' => $this->getValue());
    }
}