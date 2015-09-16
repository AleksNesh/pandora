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

class MageWorx_Adminhtml_Block_Tweaks_Adminhtml_Sales_Order_Grid extends MageWorx_Adminhtml_Block_Tweaks_Adminhtml_Sales_Order_Grid_Abstract
{    
    
    protected function _prepareColumns()
    {       
       
        if (Mage::helper('tweaks')->isOrderViewProductsColumnBackendEnable()) {
            $this->addColumnAfter('products', array(            
                'renderer'  => 'mageworx/tweaks_adminhtml_sales_order_grid_renderer_products',
                'header' => Mage::helper('sales')->__('Products'),
                'index' => 'name',       
                ), 'created_at');
        }
        return parent::_prepareColumns();
    }           

}   