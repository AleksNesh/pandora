<?php

/**
 * Extend/Override Infomodus_Upslabel module
 *
 * @category    Pan_Infomodus
 * @package     Pan_Infomodus_Upslabel
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_Infomodusupslabel_Model_Observer extends Infomodus_Upslabel_Model_Observer
{
    /**
     * =========================================================================
     * AAI HACK
     *
     * FIXES comparison of block class name to use `instanceof` to check a
     * class' ancestry instead of comparing against `get_class($block)` which
     * fails hard when another module (e.g., Xtento_GridActions) extends or
     * overwrites the block class
     * =========================================================================
     */
    public function initUpslabel($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction) {

            $controllerName = $block->getRequest()->getControllerName();
            switch ($controllerName) {
                case 'sales_order':
                    $type = 'order';
                    break;
                case 'sales_shipment':
                    $type = 'shipment';
                    break;
                case 'sales_creditmemo':
                    $type = 'creditmemo';
                    break;
                default:
                    $type = '';
                    break;
            }

            if (strlen($type) > 0) {
                $block->addItem('upslabel_pdflabels', array(
                    'label' => Mage::helper('sales')->__('Print UPS Shipping Labels'),
                    'url'   => Mage::app()->getStore()->getUrl('upslabel/adminhtml_pdflabels', array('type' => $type)),
                ));

				/* REMOVE Create Label from MassAction menu
                if ($type == 'order') {
                    $block->addItem('upslabel_autocreatelabel', array(
                        'label' => Mage::helper('sales')->__('Create UPS Labels for Orders'),
                        'url'   => Mage::app()->getStore()->getUrl('upslabel/adminhtml_autocreatelabel', array('type' => $type)),
                    ));
                }
				*/
            }
        }

        return $this;
    }
}
