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

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Renderer_Qnty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        /** @var MageWorx_OrdersPro_Helper_Data $helper */
        $helper = Mage::helper('mageworx_orderspro');

        $data = array();
        $data[] = $helper->__('Ordered').'&nbsp;'.intval($row->getData('total_qty_ordered'));
        
        $total = intval($row->getData('total_qty_invoiced'));
        if ($total>0) $data[] = $helper->__('Invoiced').'&nbsp;'.$total;
        
        $total = intval($row->getData('total_qty_shipped'));
        if ($total>0) $data[] = $helper->__('Shipped').'&nbsp;'.$total;
        
        $total = intval($row->getData('total_qty_refunded'));
        if ($total>0) $data[] = $helper->__('Refunded').'&nbsp;'.$total;
        
        $data = implode('<br/>', $data);        
        if (strpos(Mage::app()->getRequest()->getRequestString(), '/exportCsv/')) {
            $data = str_replace(array('&nbsp;','<br/>'), array(' ','|'), $data);
        }        
        return $data;      
    }
}
