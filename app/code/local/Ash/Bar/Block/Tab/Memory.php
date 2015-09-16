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
 * Memory Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Memory extends Ash_Bar_Block_Tab
{
    /**
     * Retrieve amount of memory consumed during request
     *
     * @return  string
     */
    public function getLabel()
    {
        return Mage::helper('ash_bar')->formatBytes(memory_get_usage(true));
    }

    /**
     * Before HTML render
     *
     * @return string
     */
    protected function _beforeLabel()
    {
        return;
    }

    /**
     * After HTML render
     *
     * @return string
     */
    protected function _afterLabel()
    {
        return;
    }
}
