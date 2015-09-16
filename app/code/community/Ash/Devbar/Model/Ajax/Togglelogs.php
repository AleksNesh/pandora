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
 * Toggle logging Ajax model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Model_Ajax_Togglelogs extends Ash_Devbar_Model_Ajax
{
    /**
     * Toggle Ajax request for logging
     *
     * @return  string
     */
    public function handleRequest()
    {
        $enabled = !(Mage::getStoreConfigFlag('dev/log/active'));
        $status  = ($enabled) ? 'enabled' : 'disabled';
        $label   = ($enabled) ? 'disable' : 'enable';

        Mage::getConfig()->saveConfig('dev/log/active', $enabled);

        return array(
            'status' => $status,
            'label'  => ucwords($label . ' logging'),
            'html'   => sprintf('<div data-alert class="alert-box info radius">'
                . 'Logging is %s<a href="#" class="close">&times;</a></div>', $status),
        );
    }
}
