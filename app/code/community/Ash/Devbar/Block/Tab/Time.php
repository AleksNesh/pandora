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
 * Script Time Tab Block
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Block_Tab_Time extends Ash_Devbar_Block_Tab_Abstract
{
    /**
     * Retrieve seconds to render the request
     *
     * @return  string
     */
    public function getLabel()
    {
        return number_format(Varien_Profiler::fetch('mage','sum'),2).'s';
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
