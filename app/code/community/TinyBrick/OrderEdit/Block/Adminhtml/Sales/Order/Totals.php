<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Totals//Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        
           if ($_SESSION['teo_post_total'] > $_SESSION['teo_pre_total']) {
            $this->_totals['paid'] = new Varien_Object(array(
                'code' => 'paid',
                'strong' => true,
                'value' => $_SESSION['teo_pre_total'],
                'base_value' => $_SESSION['teo_pre_total'],
                'label' => $this->helper('sales')->__('Total Paid'),
                'area' => 'footer'
            ));
        } else {
            $this->_totals['paid'] = new Varien_Object(array(
                'code' => 'paid',
                'strong' => true,
                'value' => $this->getSource()->getBaseTotalPaid(),
                'base_value' => $this->getSource()->getBaseTotalPaid(),
                'label' => $this->helper('sales')->__('Total Paid'),
                'area' => 'footer'
            ));
        }
        
        $this->_totals['refunded'] = new Varien_Object(array(
            'code'      => 'refunded',
            'strong'    => true,
            'value'     => $this->getSource()->getTotalRefunded(),
            'base_value'=> $this->getSource()->getBaseTotalRefunded(),
            'label'     => $this->helper('sales')->__('Total Refunded'),
            'area'      => 'footer'
        ));
      if ($_SESSION['teo_post_total'] > $_SESSION['teo_pre_total']) {
            $this->_totals['due'] = new Varien_Object(array(
                'code' => 'due',
                'strong' => true,
                'value' => ($_SESSION['teo_post_total'] - $_SESSION['teo_pre_total']),
                'base_value' => ($_SESSION['teo_post_total'] - $_SESSION['teo_pre_total']),
                'label' => $this->helper('sales')->__('Total Due'),
                'area' => 'footer'
            ));
        } else {
            $this->_totals['due'] = new Varien_Object(array(
                'code' => 'due',
                'strong' => true,
                'value' => $this->getSource()->getBaseTotalDue(),
                'base_value' => $this->getSource()->getBaseTotalDue(),
                'label' => $this->helper('sales')->__('Total Due'),
                'area' => 'footer'
            ));
        }
        
        return $this;
    }
}
