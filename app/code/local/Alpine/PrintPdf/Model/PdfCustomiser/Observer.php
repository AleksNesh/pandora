<?php
/**
 * Observer overrides
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2015 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Model_PdfCustomiser_Observer
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Model_PdfCustomiser_Observer extends Fooman_PdfCustomiser_Model_Observer
{

    /**
     * Observe core_block_abstract_prepare_layout_after to change URLS in
     * massaction dropdown menus to Pdf Customiser controller
     * and also to adjust Print Buttons
     *
     * @param $observer
     */
    public function addbutton($observer)
    {
        parent::addbutton($observer);

        $block = $observer->getEvent()->getBlock();
        if (
            $block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction ||
            $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
        ) {
            $replaceLinks = array(
                'pdfinvoices_order'  => 'printpdf/print/invoices',
                'pdforders_order'    => 'printpdf/print/orders',
                'pdfshipments_order' => 'printpdf/print/packingslips',
            );
            foreach ($replaceLinks as $blockName => $link) {
                $printLink = $block->getItem($blockName);
                if ($printLink) {
                    $printLink->setUrl($block->getUrl($link));
                    $printLink->setTarget('_blank');
                }
            }
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_View) {
            $block->removeButton('print');
            $block->addButton(
                'print',
                array(
                    'label'   => Mage::helper('sales')->__('Print'),
                    'class'   => 'save',
                    'onclick' => 'window.open(\'' . $this->getInvoicePrintUrl($block) . '\')'
                )
            );
        }
    }

    /**
     * @param $block
     * @return mixed
     */
    public function getInvoicePrintUrl($block)
    {
        return $block->getUrl(
            'printpdf/print/invoice',
            array(
                'invoice_id' => $block->getInvoice()->getId()
            )
        );
    }

}