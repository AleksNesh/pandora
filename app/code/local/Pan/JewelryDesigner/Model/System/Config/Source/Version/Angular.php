<?php
/**
 * Add Angular support
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pan_JewelryDesigner_Model_System_Config_Source_Version_Angular
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
            array('value' => '1.2.19', 'label' => '1.2.19 (legacy)'),
            array('value' => '1.2.20', 'label' => '1.2.20 (legacy)'),
            array('value' => '1.2.23', 'label' => '1.2.23 (legacy)'),
            array('value' => '1.3.0-beta.14', 'label' => '1.3.0-beta.14'),
            array('value' => '1.3.0-beta.15', 'label' => '1.3.0-beta.15'),
            array('value' => '1.3.0-rc.0', 'label' => '1.3.0-rc.0 (latest / release candidate)'),
        );

        return $this->_options;
    }
}
