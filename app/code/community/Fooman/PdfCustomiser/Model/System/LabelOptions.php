<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_LabelOptions
{
    /**
     * supply dropdown choices for integrated label content
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value'=> '0',
                'label'=> Mage::helper('pdfcustomiser')->__('Don\'t Use')
            ),
            array(
                'value'=> 'singleshipping',
                'label'=> Mage::helper('pdfcustomiser')->__('Single - Shipping Address Label')
            ),
            array(
                'value'=> 'singlebilling',
                'label'=> Mage::helper('pdfcustomiser')->__('Single - Billing Address Label')
            ),
            array(
                'value'=> 'double',
                'label'=> Mage::helper('pdfcustomiser')->__('Double - Both Addresses')
            ),
            array(
                'value'=> 'doublereturn',
                'label'=> Mage::helper('pdfcustomiser')->__('Double - Shipping and Store Addresses')
            ),
            array(
                'value'=> 'doubleimage',
                'label'=> Mage::helper('pdfcustomiser')->__('Double - Shipping with Store Addresses and Image')
            ),
            array(
                'value'=> 'label1-shipping',
                'label'=> Mage::helper('pdfcustomiser')->__('Custom Label Left - Shipping Address')
            ),
            array(
                'value'=> 'label2-shipping',
                'label'=> Mage::helper('pdfcustomiser')->__('Custom Label Right - Shipping Address')
            ),
            array(
                'value'=> 'label1-billing',
                'label'=> Mage::helper('pdfcustomiser')->__('Custom Label Left - Billing Address')
            ),
            array(
                'value'=> 'label2-billing',
                'label'=> Mage::helper('pdfcustomiser')->__('Custom Label Right - Billing Address')
            )
        );
    }
}