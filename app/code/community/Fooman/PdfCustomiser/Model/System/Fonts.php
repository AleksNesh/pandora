<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_Fonts
{
    /**
     * supply dropdown choices for fonts
     * generated from contents of lib/tcpdf/fonts directory
     *
     * @return array
     */
    public function toOptionArray()
    {
        $preLoadedFonts = array(
            'courier' => Mage::helper('pdfcustomiser')->__('Courier'),
            'times' => Mage::helper('pdfcustomiser')->__('Times New Roman'),
            'helvetica' => Mage::helper('pdfcustomiser')->__('Helvetica'),
            'dejavusans' => Mage::helper('pdfcustomiser')->__('DejaVuSans'),
            'dejavusansmono' => Mage::helper('pdfcustomiser')->__('DejaVuSansMono'),
            'dejavuserif' => Mage::helper('pdfcustomiser')->__('DejaVuSerif'),
            'arialunicid0-cns1' => Mage::helper('pdfcustomiser')->__('Arial Unicode Chinese CNS1'),
            'arialunicid0-gb1' => Mage::helper('pdfcustomiser')->__('Arial Unicode Chinese GB1'),
            'arialunicid0-japan1' => Mage::helper('pdfcustomiser')->__('Arial Unicode Japan1'),
            'arialunicid0-korea' => Mage::helper('pdfcustomiser')->__('Arial Unicode Korea1')
        );

        $fontDir = Mage::getBaseDir('lib') . DS . 'tcpdf' . DS . 'fonts';
        $suppressedFonts = array(
            'dejavusanscondensed',
            'dejavuserifcondensed',
            'dejavusansextralight',
            'dejavusans-extralight',
            'scheherazaderegot',
            'arialunicid0',
            'cid0kr',
            'uni2cid_ag15',
            'cid0ct',
            'chinese',
            'uni2cid_aj16',
            'cid0jp',
            'cid0cs',
            'uni2cid_ak12',
            'uni2cid_ac15'
        );

        $fontsToAdd = array();
        foreach (new DirectoryIterator($fontDir) as $fontFile) {
            if (!$fontFile->isDot() && pathinfo($fontFile, PATHINFO_EXTENSION) == 'php') {
                $filename = pathinfo($fontFile, PATHINFO_FILENAME);
                $baseFontFileName = rtrim($filename, 'bi');
                if (!array_key_exists($filename, $preLoadedFonts)
                    && !array_key_exists(
                        $baseFontFileName, $preLoadedFonts
                    )
                    && !in_array($filename, $suppressedFonts)
                    && !in_array($baseFontFileName, $suppressedFonts)
                ) {
                    $fontsToAdd[$filename] = $filename;
                }
            }
        }
        $fontsToLoad = array_merge($preLoadedFonts, $fontsToAdd);
        $returnArray = array();
        foreach ($fontsToLoad as $fontname => $label) {
            $returnArray[] = array('value' => $fontname, 'label' => $label);
        }
        return $returnArray;
    }


}
