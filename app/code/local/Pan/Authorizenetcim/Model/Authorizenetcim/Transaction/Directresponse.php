<?php

/**
 * Extend/Override TinyBrick_Authorizenetcim module
 *
 * Adds ability to partially authorize/capture payments for invoices
 * using the CIM API (and some AIM API arguments)
 *
 * @category    Pan
 * @package     Pan_Authorizenetcim
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_Authorizenetcim_Model_Authorizenetcim_Transaction_Directresponse extends Varien_Object
{
    /**
     * $_drKeys
     *
     * NOTE: the array keys are offset from the manual by -1
     *
     * @var array
     * @see http://developer.authorize.net/guides/AIM/wwhelp/wwhimpl/js/html/wwhelp.htm#href=4_TransResponse.6.2.html
     * @see http://www.authorize.net/support/AIM_guide.pdf "Fields in the Payment Gateway Response"
     */
    protected $_drKeys = array(
        0  => 'ResponseCode',
		1  => 'ResponseSubCode',
		2  => 'ResponseReasonCode',
		3  => 'ResponseReasonText',
		4  => 'AuthorizationCode',
		5  => 'AVSResponse',
		6  => 'TransactionID',
		7  => 'InvoiceNumber',
		8  => 'Description',
		9  => 'Amount',
		10 => 'Method',
		11 => 'TransactionType',
		12 => 'CustomerID',
		13 => 'FirstName',
		14 => 'LastName',
		15 => 'Company',
		16 => 'Address',
		17 => 'City',
		18 => 'State',
		19 => 'ZIPCode',
		20 => 'Country',
		21 => 'Phone',
		22 => 'Fax',
		23 => 'EmailAddress',
		24 => 'ShipToFirstName',
		25 => 'ShipToLastName',
		26 => 'ShipToCompany',
		27 => 'ShipToAddress',
		28 => 'ShipToCity',
		29 => 'ShipToState',
		30 => 'ShipToZIPCode',
		31 => 'ShipToCountry',
		32 => 'Tax',
		33 => 'Duty',
		34 => 'Freight',
		35 => 'TaxExempt',
		36 => 'PurchaseOrderNumber',
		37 => 'MD5Hash',
		38 => 'CardResponseCode',
		39 => 'CardholderAuthenticationVerificationResponse',
        // 40 - 49 are merchant defined fields
		50 => 'AccountNumber',
		51 => 'CardType',
		52 => 'SplitTenderID',
		53 => 'RequestedAmount',
		54 => 'BalanceOnCard'
    );

    public function getDirectResponseKeys()
    {
        return $this->_drKeys;
    }
}
