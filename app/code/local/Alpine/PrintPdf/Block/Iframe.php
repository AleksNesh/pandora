<?php
/**
 * Iframe block
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Block_Iframe
 *
 * @method string getPdfUrl()
 * @method Alpine_PrintPdf_Block_Iframe setPassedParameters(array $value)
 * @method array getPassedParameters()
 * @method Alpine_PrintPdf_Block_Iframe setMethod(string $value)
 * @method string getMethod()
 * @method Alpine_PrintPdf_Block_Iframe setPrinter(string $value)
 * @method string getPrinter()
 * @method Alpine_PrintPdf_Block_Iframe setPaperSizeX(string $value)
 * @method string getPaperSizeX()
 * @method Alpine_PrintPdf_Block_Iframe setPaperSizeY(string $value)
 * @method string getPaperSizeY()
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Block_Iframe extends Mage_Core_Block_Template
{

    const METHOD_GET  = 'get';
    const METHOD_POST = 'post';

    protected function _construct()
    {
        $this->setMethod(self::METHOD_GET);
    }

    /**
     * @param $value
     * @return Alpine_PrintPdf_Block_Iframe
     */
    public function setPdfUrl($value)
    {
        if (Mage::getStoreConfig('alpine_printpdf/qz/enabled')) {
            $value .= '?SID=' . Mage::getSingleton('adminhtml/session')->getSessionId();
        }
        return parent::setPdfUrl($value);
    }

    /**
     * @return string
     */
    public function getCloseWindow()
    {
        return Mage::getStoreConfig('alpine_printpdf/qz/close_window');
    }

}