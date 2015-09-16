<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_LogoBackgroundPrintingOptions
{
    /**
     * supply dropdown choices for printing of logo and background
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'no',
                'label' => Mage::helper('pdfcustomiser')->__('No')
            ),
            array(
                'value' => 'yes-logo',
                'label' => Mage::helper('pdfcustomiser')->__('Yes - logo only')
            ),
            array(
                'value' => 'yes-background',
                'label' => Mage::helper('pdfcustomiser')->__('Yes - background only')
            ),
            array(
                'value' => 'yes-all',
                'label' => Mage::helper('pdfcustomiser')->__('Yes - both logo and background')
            )
        );
    }


}