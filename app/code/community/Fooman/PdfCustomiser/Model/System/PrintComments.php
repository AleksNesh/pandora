<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_PrintComments
{
    /**
     * supply dropdown choices for printing of order history with comments
     *
     * @return array
     */

    /**
     * 0   Printing of status history and comments is disabled
     * 1   Prints all status history and comments
     * 2   Prints the status history and comments that are frontend visible only
     * 3   Prints the status history and comments that are backend visible only
     */
    const   PRINT_NONE = 0;
    const   PRINT_ALL = 1;
    const   PRINT_FRONTEND_VISIBLE = 2;
    const   PRINT_BACKEND_VISIBLE = 3;

    public function toOptionArray()
    {
        return array(
            array(
                'value'=> self::PRINT_NONE,
                'label' => Mage::helper('pdfcustomiser')->__('No')
            ),
            array(
                'value'=> self::PRINT_ALL,
                'label' => Mage::helper('pdfcustomiser')->__('All')
            ),
            array(
                'value'=> self::PRINT_FRONTEND_VISIBLE,
                'label' => Mage::helper('pdfcustomiser')->__('Frontend Visible Only')
            ),
            array(
                'value'=> self::PRINT_BACKEND_VISIBLE,
                'label' => Mage::helper('pdfcustomiser')->__('Backend Visible Only')
            )
        );
    }
}