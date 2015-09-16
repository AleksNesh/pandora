<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Toggle cache Ajax model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Model_Ajax_Togglecache extends Ash_Devbar_Model_Ajax
{
    /**
     * Toggle Ajax request for cache
     *
     * @return  string
     */
    public function handleRequest()
    {
        $enabled    = !(Mage::helper('ash_devbar/cache')->isEnabled());
        $status     = ($enabled) ? 'enabled' : 'disabled';
        $label      = ($enabled) ? 'disable' : 'enable';
        $cacheTypes = Mage::app()->useCache();

        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            if ($enabled) {
                // already off, so enable caches
                $cacheTypes[$type->getId()] = 1;
            } else {
                // already on, so disable caches
                $cacheTypes[$type->getId()] = 0;
                Mage::app()->getCacheInstance()->cleanType($type->getId());
            }
        }
        Mage::app()->saveUseCache($cacheTypes);

        return array(
            'status' => $status,
            'label'  => 'Reloading...',
            'html'   => sprintf('<div data-alert class="alert-box info radius">'
                . 'All cache types %s, refreshing page.', $status),
        );
    }
}
