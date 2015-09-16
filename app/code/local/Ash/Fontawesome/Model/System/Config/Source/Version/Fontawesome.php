<?php
/**
 * Font Awesome icon support
 *
 * @category    Ash
 * @package     Ash_Fontawesome
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Ash_Fontawesome_Model_System_Config_Source_Version_Fontawesome
{
    /**
     * Select options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Return a dropdown menu array of values
     *
     * @return  array
     */
    public function toOptionArray()
    {
        $this->_options = array(
            array('value' => '', 'label' => Mage::helper('adminhtml')->__('-- Please Select --')),
            array('value' => '4.0.3', 'label' => '4.0.3'),
            array('value' => '3.2.1', 'label' => '3.2.1'),
        );

        return $this->_options;
    }
}
