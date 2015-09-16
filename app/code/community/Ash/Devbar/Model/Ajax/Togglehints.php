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
 * Toggle template hints Ajax model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Model_Ajax_Togglehints extends Ash_Devbar_Model_Ajax
{
    /**
     * Toggle Ajax request for template hints
     *
     * @return  string
     */
    public function handleRequest()
    {
        $enabled = !(Mage::getStoreConfigFlag('dev/debug/template_hints'));
        $status  = ($enabled) ? 'enabled' : 'disabled';
        $label   = ($enabled) ? 'disable' : 'enable';

        Mage::getConfig()->saveConfig('dev/debug/template_hints',
            $enabled,
            'stores',
            Mage::app()->getStore()->getStoreId()
        );
        Mage::helper('ash_devbar/cache')->clean();

        return array(
            'status' => $status,
            'label'  => 'Reloading...',
            'html'   => sprintf('<div data-alert class="alert-box info radius">'
                . 'Template hints are %s, refreshing page.</div>', $status),
        );
    }
}
