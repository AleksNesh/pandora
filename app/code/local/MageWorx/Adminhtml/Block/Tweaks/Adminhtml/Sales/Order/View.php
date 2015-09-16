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
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Block_Tweaks_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function __construct()
    {
    	if (Mage::helper('tweaks')->isOrderViewPrevnextBottonsEnable()) {
	        if ($prevOrderUrl = $this->getPrevNextOrderUrl('<')) {
	            $this->_addButton('order_prev', array(
	                'label'     => Mage::helper('tweaks')->__('View Previous'),
	                'onclick'   => "setLocation('{$prevOrderUrl}')",
	            ));
	        }
	        parent::__construct();

	        if ($nextOrderUrl = $this->getPrevNextOrderUrl('>')){
	            $this->_addButton('order_next', array(
	                'label'     => Mage::helper('tweaks')->__('View Next'),
	                'onclick'   => "setLocation('{$nextOrderUrl}')",
	            ));
	        }
    	} else {
    		parent::__construct();
    	}
    }

    public function getPrevNextOrderUrl($direction = '>')
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $orders = Mage::getResourceModel('sales/order_collection');
        $orders->getSelect()
                ->where('entity_id '.$direction.' ?', $orderId)
                ->order('entity_id '.($direction == '>' ? 'ASC' : 'DESC'))
                ->limit(1);
	$order = $orders->getFirstItem();

        if ($order->hasData()) {
            return $this->_getUrlModel()->getUrl('*/*/view', array('order_id' => $order->getId()));
        }
        return '';
    }
}