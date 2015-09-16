<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_ShipmentDisplay
{
    /**
     * supply dropdown choices for packing slip content
     * @deprecated
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'none', 'label'=>Mage::helper('pdfcustomiser')->__('None')),
            array('value'=>'image', 'label'=>Mage::helper('pdfcustomiser')->__('Product Image')),
            //array('value'=>'barcode', 'label'=>Mage::helper('pdfcustomiser')->__('SKU Barcode'))
        );
    }


}