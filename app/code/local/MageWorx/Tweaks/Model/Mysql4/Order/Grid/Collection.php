<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Magento Tweaks extension
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @author     MageWorx Dev Team
 */

/**
 * Flat sales order grid collection
 *
 */
class MageWorx_Tweaks_Model_Mysql4_Order_Grid_Collection extends Mage_Sales_Model_Mysql4_Order_Grid_Collection
{        
    public function __construct($resource=null)
    {
        parent::__construct();
        if (Mage::helper('tweaks')->isOrderViewProductsColumnBackendEnable()) {
            $this->setFieldProductNames();
            $this->setShellRequest();
        }
    }
    

    public function setFieldProductNames()
    {              
        if ($this->getSelect()!== null) {
            //$this->getSelect()->columns(array('product_names' =>"(SELECT GROUP_CONCAT(name SEPARATOR '\n') FROM ".$this->getTable('sales/order_item')." WHERE parent_item_id IS NULL AND order_id=main_table.entity_id)"));
            $this->getSelect()->joinLeft(array('order_item'=>$this->getTable('sales/order_item')),
                    'order_item.order_id = main_table.entity_id',
                    array('name' => new Zend_Db_Expr('GROUP_CONCAT(`name` SEPARATOR \'\n\')')))
                    ->where('order_item.`parent_item_id` IS NULL')
                    ->group('main_table.entity_id');                        
        }
        return $this;
    }
    
    public function setShellRequest()
    {              
        if ($this->getSelect()!==null) {            
            $sql = $this->getSelect()->assemble();            
            $this->getSelect()->reset()->from(array('shell_request' => new Zend_Db_Expr('('.$sql.')')), '*');                                    
        }                        
        return $this;
    }
    
}
