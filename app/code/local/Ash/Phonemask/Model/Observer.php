<?php

/**
 * Ash_Phonemask
 *
 * Custom phone mask for phone/fax fields
 *
 * @category    Ash
 * @package     Ash_Phonemask
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 *
 */

class Ash_Phonemask_Model_Observer
{
    /**
     * Strip non-digit characters from phone/fax numbers in customer addresses
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function customer_address_save_before(Varien_Event_Observer $observer)
    {
        $customer_address   = $observer->getCustomerAddress();
        $cleanPhone         = $this->_cleanPhone($customer_address, 'telephone');
        $cleanFax           = $this->_cleanPhone($customer_address, 'fax');

        $customer_address->setTelephone($cleanPhone);
        $customer_address->setFax($cleanFax);
    }

    /**
     * _cleanPhone
     *
     * strip non-digit characters from phone/fax numbers
     *
     * @param  Mage_Customer_Model_Address $address
     * @param  string                      $type    # (i.e., 'fax' or 'telephone')
     * @param  string                      $pattern # regex pattern to use
     * @return string
     */
    protected function _cleanPhone(Mage_Customer_Model_Address $address, $type = 'telephone', $pattern = "/[^\d]/")
    {
        $number     = $address->getData($type);
        $cleanNum   = preg_replace($pattern, "", $number);

        return $cleanNum;
    }
}
