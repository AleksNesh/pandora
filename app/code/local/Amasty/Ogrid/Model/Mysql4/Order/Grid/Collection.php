<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Model_Mysql4_Order_Grid_Collection extends Mage_Sales_Model_Mysql4_Order_Grid_Collection
{
    /**
     * Customer mode flag
     *
     * @var bool
     */
    protected $_customerModeFlag = false;
    
    public function getSelectCountSql()
    {
        
//        if ($this->getIsCustomerMode()) {
            $this->_renderFilters();
            
            $where = $this->getSelect()->getPart(Zend_Db_Select::WHERE);
            foreach($where as $ind => $part){
                $part = strtolower($part);
                if (strpos($part, 'store_id in') !== FALSE){
                    $where[$ind] = str_replace("store_id in", "main_table.`store_id` in", $part);
                }
            }
            $this->getSelect()->setPart(Zend_Db_Select::WHERE, $where);

            $unionSelect = clone $this->getSelect();

//            $unionSelect->reset(Zend_Db_Select::COLUMNS);
//            $unionSelect->columns('main_table.entity_id');

            $unionSelect->reset(Zend_Db_Select::ORDER);
            $unionSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $unionSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

            $countSelect = clone $this->getSelect();
            $countSelect->reset();
            $countSelect->from(array('a' => $unionSelect), 'COUNT(*)');
            
            
            
//        } else {
//            $countSelect = parent::getSelectCountSql();
//        }
        return $countSelect;
    }

    /**
     * Set customer mode flag value
     *
     * @param bool $value
     * @return Mage_Sales_Model_Resource_Order_Grid_Collection
     */
    public function setIsCustomerMode($value)
    {
        $this->_customerModeFlag = (bool)$value;
        return $this;
    }

    /**
     * Get customer mode flag value
     *
     * @return bool
     */
    public function getIsCustomerMode()
    {
        return $this->_customerModeFlag;
    }
}
?>