<?php

/**
 * Ash_SlideshowExtended
 *
 * Extends the community version of Ash_Slideshow
 * to add more client specific changes
 *
 * @category    Ash
 * @package     Ash_SlideshowExtended
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ash_SlideshowExtended_Model_System_Config_Source_Show
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => 'home',
            'label' => 'Home Page Only',
        );
        // $options[] = array(
        //     'value' => 'all',
        //     'label' => 'All Pages',
        // );

        return $options;
    }
}
