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
 * Block Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Block extends Ash_Bar_Block_Tab
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setTemplate('tabs/block.phtml');
    }

    /**
     * Retrieve blocks rendered during request
     *
     * @return  string
     */
    public function getLabel()
    {
        return 'Blocks';
    }
}
