<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_AddressOptions
{
    /**
     * supply dropdown options for address choices
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'billing', 'label'=>Mage::helper('pdfcustomiser')->__('Billing Address only')),
            array('value'=>'shipping', 'label'=>Mage::helper('pdfcustomiser')->__('Shipping Address only')),
            array('value'=>'both', 'label'=>Mage::helper('pdfcustomiser')->__('Both Addresses'))
        );
    }


}