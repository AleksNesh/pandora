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
 * Clean cache Ajax model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Model_Ajax_Cleancache extends Ash_Devbar_Model_Ajax
{
    /**
     * Ajax request for cleaning cache
     *
     * @return  string
     */
    public function handleRequest()
    {
        Mage::helper('ash_devbar/cache')->clean();
        return '<div data-alert class="alert-box success radius">Cache cleared!'
             . '<a href="#" class="close">&times;</a></div>';
    }
}
