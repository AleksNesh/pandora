<?php
/**
 * Boost cachability by enabling block-level cache on strategic core Magneto
 * blocks
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @copyright   Copyright (c) 2015 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page HTML Footer model
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Cacheboost_Model_Type_Page_Html_Footer extends Ash_Cacheboost_Model_Type_Abstract
{
    /**
     * Get cache key informative items
     *
     * @return  array
     */
    public function getCacheKeyInfo()
    {
        return $this->getBlock()->getCacheKeyInfo() + parent::getDefaultKeys();
    }

    /**
     * Get tags array for saving cache
     *
     * @return  array
     */
    public function getCacheTags()
    {
        return parent::getDefaultTags();
    }
}
