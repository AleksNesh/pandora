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

class MageWorx_Tweaks_Block_Sales_Order_History extends Mage_Sales_Block_Order_History
{

    public function __construct()
    {        
        parent::__construct();        
        if (Mage::helper('tweaks')->isOrderViewProductsColumnFrontendEnable()) {
            $this->setTemplate('tweaks/sales-order-history.phtml');
            
            $orders = Mage::getResourceModel('sales/order_collection')
                ->addFieldToSelect('*')                
                ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                ->setOrder('created_at', 'desc');            
            $orders->getSelect()->columns(array('product_names' =>new Zend_Db_Expr('(SELECT GROUP_CONCAT(name SEPARATOR \'\n\') FROM '.$orders->getTable('sales/order_item').' WHERE parent_item_id IS NULL AND order_id=main_table.entity_id)')));                    
            $this->setOrders($orders);
            Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
        }
    }
    
    public function productsRender($row)
    {                                
        $products = explode("\n", $this->htmlEscape($row->getProductNames()));                
        $prCount=count($products);
        if ($prCount>3) {
            $products[$prCount-1].='<a href="" onclick="$(\'hdiv_'.$row->getRealOrderId().'\').style.display=\'none\'; $(\'a_'.$row->getRealOrderId().'\').style.display=\'block\'; return false;" style="float:right; font-weight:bold; text-decoration: none;" title="'.Mage::helper('tweaks')->__('Less..').'">↑</a>'
                .'</div>';            
            $products[2].='<a href="" id="a_'.$row->getRealOrderId().'" onclick="$(\'hdiv_'.$row->getRealOrderId().'\').style.display=\'block\'; this.style.display=\'none\'; return false;" style="float:right; font-weight:bold; text-decoration: none;" title="'.Mage::helper('tweaks')->__('More..').'">↓</a>'
                .'<div id="hdiv_'.$row->getRealOrderId().'" style="display:none">'.$products[3];
            unset($products[3]);                                             
        }        
        return implode('<br/>', $products);        
    }    
    
}
