<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Html extends Fooman_PdfCustomiser_Model_Abstract
{

    const PDFCUSTOMISER_PDF_TYPE='html';
    /**
     * return type of pdf being rendered
     *
     * @param void
     * @access public
     *
     * @return string
     */
    public function getPdfType()
    {
        return self::PDFCUSTOMISER_PDF_TYPE;
    }

    /**
     * Creates pdf from html using the tcpdf library
     * pdf is returned as object
     *
     * @param array $input storeId => HTML
     * @access public
     *
     * @return bool|Fooman_PdfCustomiser_Model_Mypdf
     */
    public function renderPdf($input)
    {
        $html = reset($input);
        $storeId = key($input);
        $pdf = $this->getMypdfModel($storeId);

        // create new helper
        /* var $helper Fooman_PdfCustomiser_Helper_Pdf_Order */
        $helper = Mage::helper('pdfcustomiser/pdf_order');

        if ($storeId) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initial = $appEmulation->startEnvironmentEmulation(
                $storeId, Mage_Core_Model_App_Area::AREA_FRONTEND, true
            );
        }

        $helper->setStoreId($storeId);
        $pdf->setStoreId($storeId);
        $pdf->setPdfHelper($helper);
        // set standard pdf info
        $pdf->SetStandard($helper);
        $pdf->addPage();
        $pdf->writeHTML($html);
        $pdf->endPage();
        $pdf->lastPage();
        if ($storeId) {
            $appEmulation->stopEnvironmentEmulation($initial);
        }
        return $pdf;
    }

}
