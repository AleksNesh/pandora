<?php

/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 *
 */
class Ash_Slideshow_Model_System_Config_Source_Slideshow
{
    public function toOptionArray()
    {
        $collection = Mage::getModel('ash_slideshow/slideshow')->getCollection();
        $collection->addFieldToFilter('status', array('eq' => 1));

        $data       = array();
        foreach ($collection as $show) {
            $data[$show['id']] = $show['slideshow_name'];
        }

        return $data;
    }
}
