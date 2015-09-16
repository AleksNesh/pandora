<?php
/**
 * Observer overrides
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Model_EmailAttachments_Observer
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Model_EmailAttachments_Observer extends Fooman_EmailAttachments_Model_Observer
{

    /**
     * Observe core_block_abstract_prepare_layout_after to add a Print Orders
     * massaction to the actions dropdown menu
     *
     * @param $observer
     */
    public function addbutton($observer)
    {
        parent::addbutton($observer);

        $block = $observer->getEvent()->getBlock();

        // Add target _blank to option
        if (
            $block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction ||
            $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
        ) {
            if (
                $block->getRequest()->getControllerName() == 'sales_order' ||
                $block->getRequest()->getControllerName() == 'adminhtml_sales_order' ||
                $block->getRequest()->getControllerName() == 'sales_archive'
            ) {
                $printLink = $block->getItem('pdforders_order');
                if ($printLink) {
                    $printLink->setTarget('_blank');
                }
            }
        }
    }

}