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
 * URI Grid Renderer
 *
 * @category    Ash_Up
 * @package     Ash_Up_Block
 */
class Ash_Up_Block_Adminhtml_Extension_Renderer_Uri
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render column
     *
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $uri = isset($row['download_uri']) ? (string)$row['download_uri'] : null;

        return $uri ? '<a href="' . $uri . '" title="' . $uri . '">Download</a>' : '';
    }
}
