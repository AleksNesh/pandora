<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_ColumnsSort
{
    /**
     * supply dropdown choices for column sorting
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('pdfcustomiser')->__('Default')),
            array('value' => 'Name', 'label' => Mage::helper('catalog')->__('Product Name')),
            array('value' => 'Sku', 'label' => Mage::helper('sales')->__('SKU')),
            array('value' => 'price', 'label' => Mage::helper('sales')->__('Price')),
            array('value' => 'discount', 'label' => Mage::helper('sales')->__('Discount')),
            array('value' => 'qty', 'label' => Mage::helper('sales')->__('Qty')),
            array('value' => 'tax', 'label' => Mage::helper('sales')->__('Tax')),
            array('value' => 'subtotal', 'label' => Mage::helper('sales')->__('Subtotal')),
            array('value' => 'custom', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column')),
            array('value' => 'custom2', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 2')),
            array('value' => 'custom3', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 3')),
            array('value' => 'custom4', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 4')),
            array('value' => 'custom5', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 5'))
        );
    }
}
