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
 * Profiler Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Profiler extends Mage_Core_Block_Profiler
{
    /**
     * Overrides core method to always return nothing. Prevents core profiler
     * formatting from being injected into toolbar.
     *
     * @return void
     */
    protected function _toHtml()
    {
        return;
    }
}
