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
 * Abstract type model
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @author      August Ash Team <core@augustash.com>
 */
abstract class Ash_Cacheboost_Model_Type_Abstract extends Varien_Object
    implements Ash_Cacheboost_Model_Type_Interface
{
    /**
     * Block instance
     *
     * @var Mage_Core_Block_Abstract
     */
    protected $_block = null;

    /**
     * Get block instance
     *
     * @return  Mage_Core_Block_Abstract
     */
    public function getBlock()
    {
        return $this->_block;
    }

    /**
     * Set block instance
     *
     * @param   Mage_Core_Block_Abstract $block
     * @return  Ash_Cacheboost_Model_Type_Abstract
     */
    public function setBlock(Mage_Core_Block_Abstract $block)
    {
        $this->_block = $block;
        return $this;
    }

    /**
     * Get block cache life time (defaults to 4 hours)
     *
     * @return  int
     */
    public function getCacheLifetime()
    {
        return 14400;
    }

    /**
     * Get Key for caching block content
     *
     * @return  string
     */
    public function getCacheKey()
    {
        /**
         * Don't prevent recalculation by saving generated cache key
         * because of ability to render single block instance with different data
         */
        $key = $this->getCacheKeyInfo();
        $key = array_values($key); // ignore array keys
        $key = implode('|', $key);
        $key = sha1($key);
        return $key;
    }

    /**
     * Create a cache key based on a request parameter key/value pair
     *
     * @param   string $key
     * @return  string
     */
    public static function getParamKey($key)
    {
        $value = Mage::app()->getRequest()->getParam($key, false);
        return ($value) ? $key . '_' . $value : '';
    }

    /**
     * Default cache keys
     *
     * @return  array
     */
    public static function getDefaultKeys()
    {
        return array(
            (int)Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
        );
    }

    /**
     * Default cache tags
     *
     * @return  array
     */
    public static function getDefaultTags()
    {
        return array(
            Mage_Core_Block_Abstract::CACHE_GROUP,
            Mage_Core_Model_App::CACHE_TAG,
            Mage_Core_Model_Store::CACHE_TAG,
        );
    }
}
