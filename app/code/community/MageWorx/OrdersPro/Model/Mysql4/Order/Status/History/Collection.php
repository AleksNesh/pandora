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
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_Mysql4_Order_Status_History_Collection extends Mage_Sales_Model_Mysql4_Order_Status_History_Collection
{                  
    
    public function __construct($resource=null)
    {
        parent::__construct();        
        
        if (Mage::helper('mageworx_orderspro')->isEnabled() && $this->getSelect()!==null) {
                                    
            $this->getSelect()->joinLeft(array('upload_files_tbl'=>$this->getTable('mageworx_orderspro/upload_files')),
                    'upload_files_tbl.history_id = main_table.entity_id',                    
                    array('file_id'=>'entity_id', 'file_name', 'file_size')
            );            
        }            
    }
    
}