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

class MageWorx_OrdersPro_Model_System_Config_Source_Orders_Customer_Grid
{   

    public function toOptionArray($isMultiselect=false) {
        $helper = Mage::helper('mageworx_orderspro');
        $options = array(            
            array('value'=>'increment_id', 'label'=> Mage::helper('customer')->__('Order #')),            
            array('value'=>'created_at', 'label'=> Mage::helper('customer')->__('Purchase On')),
            
            array('value'=>'product_names', 'label'=> $helper->__('Product Name(s)')),
            array('value'=>'product_skus', 'label'=> $helper->__('SKU(s)')),
            array('value'=>'product_options', 'label'=> $helper->__('Product Option(s)')),
            
            array('value'=>'qnty', 'label'=> $helper->__('Qnty')),
            array('value'=>'weight', 'label'=> $helper->__('Weight')),
            
            array('value'=>'billing_name', 'label'=> Mage::helper('customer')->__('Bill to Name')),
            array('value'=>'shipping_name', 'label'=> Mage::helper('customer')->__('Shipped to Name')),
            array('value'=>'shipping_method', 'label'=> $helper->__('Shipping Method')),
            array('value'=>'tracking_number', 'label'=> $helper->__('Tracking Number')),
            array('value'=>'shipped', 'label'=> $helper->__('Shipped')),
            array('value'=>'customer_email', 'label'=> $helper->__('Customer Email')),
            array('value'=>'customer_group', 'label'=> $helper->__('Customer Group')),
            array('value'=>'payment_method', 'label'=> $helper->__('Payment Method')),
            
            array('value'=>'base_tax_amount', 'label'=> $helper->__('Tax Amount (Base)')),
            array('value'=>'tax_amount', 'label'=> $helper->__('Tax Amount (Purchased)')),
            
            array('value'=>'coupon_code', 'label'=> $helper->__('Coupon Code')),
            array('value'=>'base_discount_amount', 'label'=> $helper->__('Discount (Base)')),
            array('value'=>'discount_amount', 'label'=> $helper->__('Discount (Purchased)')),            
            
            array('value'=>'base_internal_credit', 'label'=> $helper->__('Internal Credit (Base)')), // 20
            array('value'=>'internal_credit', 'label'=> $helper->__('Internal Credit (Purchased)')), // 21
            
            array('value'=>'base_total_refunded', 'label'=> $helper->__('Total Refunded (Base)')),
            array('value'=>'total_refunded', 'label'=> $helper->__('Total Refunded (Purchased)')),
            
            array('value'=>'grand_total', 'label'=> Mage::helper('customer')->__('Order Total')),
            
            array('value'=>'order_comment', 'label'=> $helper->__('Order Comment(s)')),
            
            array('value'=>'order_group', 'label'=> $helper->__('Group')),
            array('value'=>'store_id', 'label'=> $helper->__('Bought From')),
            array('value'=>'is_edited', 'label'=> $helper->__('Edited')),
            array('value'=>'status', 'label'=> Mage::helper('sales')->__('Status')),
            array('value'=>'action', 'label'=> Mage::helper('customer')->__('Action'))
        );
        
        if (!Mage::getConfig()->getModuleConfig('MageWorx_CustomerCredit')->is('active', true)) {
            unset($options[20]); // Internal Credit (Base)
            unset($options[21]); // Internal Credit (Purchased)
        }                
        //if (!$isMultiselect) array_pop($options);

        return $options;
    }
}