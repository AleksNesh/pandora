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
 * System Tab Block
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Block_Tab_System extends Ash_Devbar_Block_Tab_Abstract
{
    /**
     * Constructor
     *
     * @return  void
     */
    public function _construct()
    {
        $this->setTemplate('tabs/system.phtml');
    }

    /**
     * Retrieve seconds to render the request
     *
     * @return  string
     */
    public function getLabel()
    {
        return 'System';
    }
}
