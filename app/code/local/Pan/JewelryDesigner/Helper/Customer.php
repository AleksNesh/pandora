<?php
/**
 * Core module for providing common functionality between BraceletBuilder and other related submodules
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Pan_JewelryDesigner Customer helper
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @author      August Ash Team <core@augustash.com>
 */
class Pan_JewelryDesigner_Helper_Customer extends Mage_Core_Helper_Abstract
{
    public function isCustomerLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();

    }

    public function getCustomer()
    {
        $loggedIn = $this->isCustomerLoggedIn();
        return $customer = ($loggedIn) ? $this->getCustomerSession()->getCustomer() : null;
    }

    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
