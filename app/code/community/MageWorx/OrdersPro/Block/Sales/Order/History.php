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
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Block_Sales_Order_History extends MageWorx_OrdersPro_Block_Sales_Order_History_Abstract
{

    public function __construct()
    {        
        parent::__construct();
        if (Mage::helper('mageworx_orderspro')->isHideDeletedOrdersForCustomers()) {
            $orders = Mage::getResourceModel('mageworx_orderspro/order_collection')
                ->addFieldToSelect('*')                
                ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))                
                ->hideDeletedGroup()
                ->setOrder('created_at', 'desc');            
            $this->setOrders($orders);
            Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
        }
        if ((string)Mage::getConfig()->getModuleConfig('Innoexts_Warehouse')->active=='true') $this->getOrders()->setFlag('appendStockIds');
    }        
    
}
