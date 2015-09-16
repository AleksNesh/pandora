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
 * Extensions Grid Container
 *
 * @category    Ash_Up
 * @package     Ash_Up_Block
 */
class Ash_Up_Block_Adminhtml_Extension extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Internal constructor
     *
     * @return  void
     */
    public function __construct()
    {
        $this->_blockGroup     = 'ash_up';
        $this->_controller     = 'adminhtml_extension';
        $this->_headerText     = Mage::helper('ash_up')->__('Ash Installer');

        parent::__construct();
        $this->_removeButton('add');
    }
}
