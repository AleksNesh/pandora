<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_LogoPlacementOptions
{
    /**
     * supply dropdown choices for placement of logo
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'auto',
                'label' => Mage::helper('pdfcustomiser')->__('automatic') .' '. Mage::helper('pdfcustomiser')->__('left')
            ),
            array(
                'value' => 'auto-right',
                'label' => Mage::helper('pdfcustomiser')->__('automatic') . ' ' . Mage::helper('pdfcustomiser')->__('right')
            ),
            array(
                'value' => 'no-scaling',
                'label' => Mage::helper('pdfcustomiser')->__('no-scaling'). ' '. Mage::helper('pdfcustomiser')->__('left')
            ),
            array(
                'value' => 'no-scaling-right',
                'label' => Mage::helper('pdfcustomiser')->__('no-scaling') . ' '. Mage::helper('pdfcustomiser')->__('right')
            ),
            array(
                'value' => 'manual',
                'label' => Mage::helper('pdfcustomiser')->__('manual')
            )
        );
    }


}