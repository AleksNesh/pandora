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
 * Version Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Version extends Ash_Bar_Block_Tab
{
    /**
     * Retrieve current Magento version number.
     *
     * @return  string
     */
    public function getLabel()
    {
        return '<span class="label label-info">v ' . Mage::getVersion() . '</span>';
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
