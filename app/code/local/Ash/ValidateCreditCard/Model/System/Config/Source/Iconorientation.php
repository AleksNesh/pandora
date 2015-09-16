<?php
/**
 * Ash_ValidateCreditCard
 *
 * Validate & recognize credit credit card type via IIN (issuer identification number)
 *
 * @category    Ash
 * @package     Ash_ValidateCreditCard
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Used in creating options for Ash_ValidateCreditCard cards_orientation
 * config value selection
 */
class Ash_ValidateCreditCard_Model_System_Config_Source_Iconorientation
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'horizontal',
                'label' => Mage::helper('adminhtml')->__('Horizontal')
            ),
        );
    }
}
