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
 * Catalog Abstract model
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @author      August Ash Team <core@augustash.com>
 */
abstract class Ash_Cacheboost_Model_Type_Catalog_Abstract
    extends Ash_Cacheboost_Model_Type_Abstract
{
    /**
     * Product ID
     *
     * @var int
     */
    protected $_productId = null;

    /**
     * Attempt to retrieve a specific product ID
     *
     * @return  int
     */
    public function getProductId()
    {
        if ($this->_productId === null) {
            if ($this->getBlock() && $this->getBlock()->getProduct() !== null) {
                $this->_productId = $this->getBlock()->getProduct()->getId();
            } else {
                $this->_productId = Mage::app()->getRequest()->getParam('id');
            }
        }

        return $this->_productId;
    }

    /**
     * Gather parameters on category view pages as part of the cache key
     *
     * @param   string $prefix
     * @return  string
     */
    protected function _generateCategoryKey($prefix='')
    {
        $params = Mage::app()->getRequest()->getParams();

        if (!isset($params['limit'])) {
            $session       = Mage::getSingleton('catalog/session');
            $sessionParams = array(
                'limit_page'     => 'limit',
                'display_mode'   => 'mode',
                'sort_order'     => 'order',
                'sort_direction' => 'dir',
            );
            foreach ($sessionParams as $key => $value) {
                if ($session->hasData($key)) {
                    $params[$value] = $session->getData($key);
                }
            }
        }

        unset($params['id']);
        ksort($params);
        $cacheKey = '';
        foreach ($params as $key => $value) {
            $cacheKey .= '_' . $key . ':' . $value;
        }

        return md5($prefix . $cacheKey);
    }
}
