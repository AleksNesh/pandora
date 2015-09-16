<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cache data helper
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Helper_Cache extends Mage_Core_Helper_Abstract
{
    /**
     * Check if any cache types are enabled
     *
     * @return  bool
     */
    static public function isEnabled()
    {
        $count = 0;
        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            if ($type->getData('status') == 1) {
                $count++;
            }
        }

        return ($count > 0);
    }

    /**
     * Clear Magento system cache
     *
     * @param  array $tags
     * @return void
     */
    static public function clean($tags=array())
    {
        Mage::app()->cleanCache($tags);
    }
}
