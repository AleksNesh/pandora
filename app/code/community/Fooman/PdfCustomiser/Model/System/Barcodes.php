<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_Barcodes
{
    /**
     * supply dropdown choices for types of barcodes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value'=> 'C39',
                'label'=> Mage::helper('pdfcustomiser')->__('CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.')
            ),
            array('value'=> 'C39+', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 39 with checksum')),
            array('value'=> 'C39E', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 39 EXTENDED')),
            array('value'=> 'C39E+', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 39 EXTENDED + CHECKSUM')),
            array('value'=> 'S25', 'label'=> Mage::helper('pdfcustomiser')->__('Standard 2 of 5')),
            array('value'=> 'S25+', 'label'=> Mage::helper('pdfcustomiser')->__('Standard 2 of 5 + CHECKSUM')),
            array('value'=> 'I25', 'label'=> Mage::helper('pdfcustomiser')->__('Interleaved 2 of 5')),
            array('value'=> 'I25+', 'label'=> Mage::helper('pdfcustomiser')->__('Interleaved 2 of 5 + CHECKSUM')),
            array('value'=> 'C128', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 128')),
            array('value'=> 'C128A', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 128 A')),
            array('value'=> 'C128B', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 128 B')),
            array('value'=> 'C128C', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 128 C')),
            array('value'=> 'EAN2', 'label'=> Mage::helper('pdfcustomiser')->__('EAN 2')),
            array('value'=> 'EAN5', 'label'=> Mage::helper('pdfcustomiser')->__('EAN 5')),
            array('value'=> 'EAN8', 'label'=> Mage::helper('pdfcustomiser')->__('EAN 8')),
            array('value'=> 'EAN13', 'label'=> Mage::helper('pdfcustomiser')->__('EAN 13')),
            array('value'=> 'UPCA', 'label'=> Mage::helper('pdfcustomiser')->__('UPC-A')),
            array('value'=> 'UPCE', 'label'=> Mage::helper('pdfcustomiser')->__('UPC-E')),
            array('value'=> 'MSI', 'label'=> Mage::helper('pdfcustomiser')->__('MSI (Variation of Plessey code)')),
            array('value'=> 'MSI+', 'label'=> Mage::helper('pdfcustomiser')->__('MSI + CHECKSUM (modulo 11)')),
            array('value'=> 'POSTNET', 'label'=> Mage::helper('pdfcustomiser')->__('POSTNET')),
            array('value'=> 'PLANET', 'label'=> Mage::helper('pdfcustomiser')->__('PLANET')),
            array(
                'value'=> 'RMS4CC',
                'label'=> Mage::helper('pdfcustomiser')->__(
                    'RMS4CC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)'
                )
            ),
            array('value'=> 'KIX', 'label'=> Mage::helper('pdfcustomiser')->__('KIX (Klant index - Customer index)')),
            array(
                'value'=> 'IMB',
                'label'=> Mage::helper('pdfcustomiser')->__('IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200')
            ),
            array('value'=> 'CODABAR', 'label'=> Mage::helper('pdfcustomiser')->__('CODABAR')),
            array('value'=> 'CODE11', 'label'=> Mage::helper('pdfcustomiser')->__('CODE 11')),
            array('value'=> 'PHARMA', 'label'=> Mage::helper('pdfcustomiser')->__('PHARMACODE')),
            //array('value'=>'PHARMA2T', 'label'=>Mage::helper('pdfcustomiser')->__('PHARMACODE TWO-TRACKS'))
        );
    }
}