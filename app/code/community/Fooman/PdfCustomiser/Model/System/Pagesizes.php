<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_Pagesizes
{
    /**
     * supply dropdown choices for page size
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'A4', 'label'=>Mage::helper('pdfcustomiser')->__('A4')),
            array('value'=>'LETTER', 'label'=>Mage::helper('pdfcustomiser')->__('letter'))
        );
    }

}