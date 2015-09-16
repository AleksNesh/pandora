<?php
/**
 * Overrides for Data helper
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Helper_PdfCustomiser_Data
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Helper_PdfCustomiser_Data extends Fooman_PdfCustomiser_Helper_Data
{

    /**
     * Return url to print single order from order > view
     *
     * @param $block
     * @return string
     */
    protected function getPrintUrl($block)
    {
        if (Mage::helper('core')->isModuleEnabled('Fooman_PdfCustomiser')) {
            return $block->getUrl(
                'printpdf/print/order',
                array('order_id' => $block->getOrder()->getId())
            );
        } else {
            return parent::getPrintUrl($block);
        }
    }

    /**
     * Changing onclick for Print button
     *
     * @param $block
     */
    public function addButton($block)
    {
        parent::addButton($block);
        $block->removeButton('print');
        $block->addButton(
            'print', array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'window.open(\'' . $this->getPrintUrl($block) . '\', \'_blank\')'
            )
        );
    }

}