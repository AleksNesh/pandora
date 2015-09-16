<?php
/**
 * Ash Up Extension
 *
 * Management interface for keeping Ash core extensions updated.
 *
 * @category    Ash
 * @package     Ash_Up
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Action Grid Renderer
 *
 * @category    Ash_Up
 * @package     Ash_Up_Block
 */
class Ash_Up_Block_Adminhtml_Extension_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render column
     *
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $ashup = Mage::getConfig()->getNode("modules/{$row->getExtensionName()}/ashup");

        return isset($ashup['changelog']) ? '<a href="' . $ashup['changelog']
            . '">' . $this->__('Changelog') . '</a>' : '';
    }
}
