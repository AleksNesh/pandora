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

class MageWorx_OrdersPro_Model_SalesRule_Quote_Discount extends MageWorx_OrdersPro_Model_SalesRule_Quote_Discount_Abstract
{    

    protected function _aggregateItemDiscount($item) {
        $this->_transferOldItemDiscount($item); //transfer old discount
        return parent::_aggregateItemDiscount($item);
    }
    
    protected function _transferOldItemDiscount($item) {        
        if (!Mage::helper('mageworx_orderspro')->isEnabled()) return $this;
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrderId();
        if (!$orderId) return $this;
        
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) return $this;
        
        //if (!$order->getAppliedRuleIds()) return $this;       
        
        
        $data = Mage::app()->getRequest()->getPost('order');
        if (isset($data['coupon']['code']) && empty($data['coupon']['code'])) {
            Mage::getSingleton('adminhtml/session_quote')->setCouponCodeIsDeleted(true);
        }
        
        $quote = $item->getQuote();
        // if new coupon or deleted - discount do not touch
        if (Mage::getSingleton('adminhtml/session_quote')->getCouponCodeIsDeleted() || ($quote->getCouponCode() && $quote->getCouponCode()!=$order->getCouponCode())) return $this;
        
        $quote->setAppliedRuleIds($order->getAppliedRuleIds());
        
        
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductId()==$item->getProductId()) {
                if ($orderItem->getAppliedRuleIds() && $orderItem->getBaseDiscountAmount()>0) {
                    $baseDiscount = ($orderItem->getBaseDiscountAmount() / $orderItem->getQtyOrdered()) * $item->getQty();
                    $discount = ($orderItem->getDiscountAmount() / $orderItem->getQtyOrdered()) * $item->getQty();                    
                    $item->setBaseDiscountAmount($baseDiscount)->setDiscountAmount($discount)->setAppliedRuleIds($orderItem->getAppliedRuleIds());
                } else {
                    $item->setBaseDiscountAmount(0)->setDiscountAmount(0)->setAppliedRuleIds(null);
                }                
                return $this;        
            }
        }
    }
    
    
}
