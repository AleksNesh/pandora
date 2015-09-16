<?php
/**
 * Ash_ValidateCreditCard
 *
 * Validate & recognize credit card type via IIN (issuer identification number)
 *
 * Purpose of extending  Mage_Payment_Block_Form_Ccsave:
 *     + set the template for the credit card form
 *
 * @category    Ash
 * @package     Ash_ValidateCreditCard
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ash_ValidateCreditCard_Block_Payment_Form_Ccsave extends Mage_Payment_Block_Form_Ccsave
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ash_validatecreditcard/payment/form/ccsave.phtml');
    }
}
