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
 * Request Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Request extends Ash_Bar_Block_Tab
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setTemplate('tabs/request.phtml');
    }

    /**
     * Retrieve details about the current request
     *
     * @return  string
     */
    public function getLabel()
    {
        return 'Request';
    }
}
