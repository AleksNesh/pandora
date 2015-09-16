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
 * Model Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Model extends Ash_Bar_Block_Tab
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setTemplate('tabs/model.phtml');
    }

    /**
     * Retrieve models instantiated during the request
     *
     * @return  string
     */
    public function getLabel()
    {
        return 'Models';
    }
}
