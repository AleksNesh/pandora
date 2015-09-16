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
 * Memory Usage Tab Block
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Block_Tab_Memory extends Ash_Devbar_Block_Tab_Abstract
{
    /**
     * Retrieve amount of memory consumed during request
     *
     * @return  string
     */
    public function getLabel()
    {
        return Mage::helper('ash_devbar')->formatBytes(memory_get_usage(true));
    }

    /**
     * Before HTML render
     *
     * @return string
     */
    protected function _beforeLabel()
    {
        return '<span class="secondary radius label">';
    }

    /**
     * After HTML render
     *
     * @return string
     */
    protected function _afterLabel()
    {
        return '</span>';
    }
}
