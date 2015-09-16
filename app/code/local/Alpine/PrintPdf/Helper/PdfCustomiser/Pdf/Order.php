<?php
/**
 * Overrides for PDF Order helper
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Helper_PdfCustomiser_Pdf_Order
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Helper_PdfCustomiser_Pdf_Order extends Fooman_PdfCustomiser_Helper_Pdf_Order
{
    /**
     * Override for order settings
     *
     * @return string
     */
    public function getNewWindow()
    {
        if (!isset($this->_parameters['allnewwindow'])) {
            $this->_parameters['allnewwindow'] = Mage::getStoreConfigFlag('alpine_printpdf/order/allnewwindow') ? 'D' : 'I';
        }
        return $this->_parameters['allnewwindow'];
    }

}