<?php
/**
 * Add Jquery/Jquery UI support
 *
 * @category    Ash
 * @package     Ash_Jquery
 * @copyright   Copyright (c) 2013 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Ash_Jquery_Model_System_Config_Source_Version_Jquery
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
            array('value' => '1.10.2', 'label' => '1.10.2 (legacy)'),
            array('value' => '1.11.0', 'label' => '1.11.0'),
            array('value' => '2.1.0', 'label' => '2.1.0'),
        );

        return $this->_options;
    }
}
