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
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_System_Config_Source_Orders_Grid
{   

    public function toOptionArray($isMultiselect=false) {
        $helper = Mage::helper('mageworx_orderspro');
        
        $options = array(            
            array('value'=>'real_order_id', 'label'=> Mage::helper('sales')->__('Order #')),
            array('value'=>'store_id', 'label'=> Mage::helper('sales')->__('Purchased From (Store)')),
            array('value'=>'created_at', 'label'=> Mage::helper('sales')->__('Purchased On')),
            
            array('value'=>'product_names', 'label'=> $helper->__('Product Name(s)')),
            array('value'=>'product_skus', 'label'=> $helper->__('SKU(s)')),
            array('value'=>'product_options', 'label'=> $helper->__('Product Option(s)')),
            
            array('value'=>'qnty', 'label'=> $helper->__('Qnty')),
            array('value'=>'weight', 'label'=> $helper->__('Weight')),
            
            array('value'=>'billing_name', 'label'=> Mage::helper('sales')->__('Bill to Name')),
            array('value'=>'shipping_name', 'label'=> Mage::helper('sales')->__('Ship to Name')),
            
            array('value'=>'billing_company', 'label'=> $helper->__('Bill to Company')),
            array('value'=>'shipping_company', 'label'=> $helper->__('Ship to Company')),
            
            array('value'=>'billing_street', 'label'=> $helper->__('Bill to Street')),
            array('value'=>'shipping_street', 'label'=> $helper->__('Ship to Street')),
            
            array('value'=>'billing_city', 'label'=> $helper->__('Bill to City')),
            array('value'=>'shipping_city', 'label'=> $helper->__('Ship to City')),
            
            array('value'=>'billing_region', 'label'=> $helper->__('Bill to State')),
            array('value'=>'shipping_region', 'label'=> $helper->__('Ship to State')),
            
            array('value'=>'billing_country', 'label'=> $helper->__('Bill to Country')),
            array('value'=>'shipping_country', 'label'=> $helper->__('Ship to Country')),
            
            array('value'=>'billing_postcode', 'label'=> $helper->__('Billing Postcode')),
            array('value'=>'shipping_postcode', 'label'=> $helper->__('Shipping Postcode')),
            
            array('value'=>'billing_telephone', 'label'=> $helper->__('Billing Telephone')),
            array('value'=>'shipping_telephone', 'label'=> $helper->__('Shipping Telephone')),            
            
            array('value'=>'shipping_method', 'label'=> $helper->__('Shipping Method')),
            array('value'=>'tracking_number', 'label'=> $helper->__('Tracking Number')),
            
            array('value'=>'shipped', 'label'=> $helper->__('Shipped')),
            array('value'=>'customer_email', 'label'=> $helper->__('Customer Email')),
            array('value'=>'customer_group', 'label'=> $helper->__('Customer Group')),
            array('value'=>'payment_method', 'label'=> $helper->__('Payment Method')),

            array('value'=>'base_subtotal', 'label'=> $helper->__('Subtotal (Base)')),
            array('value'=>'subtotal', 'label'=> $helper->__('Subtotal (Purchased)')),

            array('value'=>'base_shipping_amount', 'label'=> $helper->__('Shipping Amount (Base)')),
            array('value'=>'shipping_amount', 'label'=> $helper->__('Shipping Amount (Purchased)')),

            array('value'=>'base_tax_amount', 'label'=> $helper->__('Tax Amount (Base)')),
            array('value'=>'tax_amount', 'label'=> $helper->__('Tax Amount (Purchased)')),
                        
            array('value'=>'coupon_code', 'label'=> $helper->__('Coupon Code')),            
            array('value'=>'base_discount_amount', 'label'=> $helper->__('Discount (Base)')),
            array('value'=>'discount_amount', 'label'=> $helper->__('Discount (Purchased)')),
            
            array('value'=>'base_internal_credit', 'label'=> $helper->__('Internal Credit (Base)')), // 35
            array('value'=>'internal_credit', 'label'=> $helper->__('Internal Credit (Purchased)')), // 36
            
            array('value'=>'base_total_refunded', 'label'=> $helper->__('Total Refunded (Base)')),
            array('value'=>'total_refunded', 'label'=> $helper->__('Total Refunded (Purchased)')),
            
            array('value'=>'base_grand_total', 'label'=> Mage::helper('sales')->__('G.T. (Base)')),
            array('value'=>'grand_total', 'label'=> Mage::helper('sales')->__('G.T. (Purchased)')),
            
            array('value'=>'order_comment', 'label'=> $helper->__('Order Comment(s)')),
            
            array('value'=>'order_group', 'label'=> $helper->__('Group')),
            array('value'=>'is_edited', 'label'=> $helper->__('Edited')),
            
            array('value'=>'status', 'label'=> Mage::helper('sales')->__('Status')),
            array('value'=>'action', 'label'=> Mage::helper('sales')->__('Action'))
        );
            
        if (!Mage::getConfig()->getModuleConfig('MageWorx_CustomerCredit')->is('active', true)) {
            unset($options[35]); // Internal Credit (Base)
            unset($options[36]); // Internal Credit (Purchased)
        }
        
        //if (!$isMultiselect) array_pop($options);

        return $options;
    }
}