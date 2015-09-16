<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_Columns
{
    /**
     * supply dropdown choices for supported columns
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'position', 'label' => Mage::helper('pdfcustomiser')->__('Position')),
            array('value' => 'name', 'label' => Mage::helper('catalog')->__('Product Name')),
            array('value' => 'sku', 'label' => Mage::helper('sales')->__('SKU')),
            array(
                'value' => 'name-sku',
                'label' => Mage::helper('sales')->__('Product') . ' + ' . Mage::helper('sales')->__('SKU')
            ),
            array('value' => 'barcode', 'label' => Mage::helper('pdfcustomiser')->__('SKU Barcode')),
            array('value' => 'image', 'label' => Mage::helper('pdfcustomiser')->__('Product Image')),
            array('value' => 'price', 'label' => Mage::helper('sales')->__('Price')),
            array('value' => 'discount', 'label' => Mage::helper('sales')->__('Discount')),
            array('value' => 'qty', 'label' => Mage::helper('sales')->__('Qty')),
            array('value' => 'qty_ordered', 'label' => Mage::helper('sales')->__('Qty Ordered')),
            array('value' => 'qty_backordered', 'label' => Mage::helper('sales')->__('Qty Back Ordered')),
            array('value' => 'qty_detailed', 'label' => Mage::helper('pdfcustomiser')->__('Qty Detailed')),
            array('value' => 'qty_stock', 'label' => Mage::helper('sales')->__('Stock Qty')),
            array('value' => 'item_status', 'label' => Mage::helper('sales')->__('Item Status')),
            array('value' => 'tax', 'label' => Mage::helper('sales')->__('Tax')),
            array('value' => 'taxrate', 'label' => Mage::helper('tax')->__('Tax Rate')),
            array('value' => 'subtotal', 'label' => Mage::helper('sales')->__('Subtotal')),
            array('value' => 'subtotal2', 'label' => Mage::helper('pdfcustomiser')->__('Alternative Subtotal')),
            array('value' => 'rowtotal2', 'label' => Mage::helper('sales')->__('Row Total')),
            array('value' => 'custom', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column')),
            array('value' => 'custom2', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 2')),
            array('value' => 'custom3', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 3')),
            array('value' => 'custom4', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 4')),
            array('value' => 'custom5', 'label' => Mage::helper('pdfcustomiser')->__('Custom Column' . ' 5'))
        );
    }
}
