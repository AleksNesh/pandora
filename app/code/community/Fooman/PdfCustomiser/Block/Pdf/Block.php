<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Block_Pdf_Block extends Fooman_PdfCustomiser_Block_Pdf_Abstract
{
    /**
     * retrieve tax summary from pdf object
     *
     * @return array
     * @access public
     */
    public function OutputTaxSummary()
    {
        return $this->getPdfHelper()->OutputTaxSummary(
            $this->getPdf()->getTaxTotal(),
            $this->getPdf()->getTaxAmount()
        );
    }
}