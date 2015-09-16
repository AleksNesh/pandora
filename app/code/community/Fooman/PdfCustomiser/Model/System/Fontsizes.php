<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_Fontsizes
{
    /**
     * supply dropdown choices for fontsizes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'8', 'label'=>8),
            array('value'=>'9.5', 'label'=>9.5),
            array('value'=>'10', 'label'=>10),
            array('value'=>'10.5', 'label'=>10.5),
            array('value'=>'11', 'label'=>11),
            array('value'=>'12', 'label'=>12),
            array('value'=>'14', 'label'=>14),
            array('value'=>'16', 'label'=>16),
            array('value'=>'18', 'label'=>18),
            array('value'=>'20', 'label'=>20)
        );
    }


}