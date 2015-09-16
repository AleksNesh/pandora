# Ash_ValidateCreditCard


## Overview

Detects and validates credit card numbers. It'll tell you the credit card type (no need for  credit card type drop downs) and perform client-side validation for the number length and Luhn checksum for the type of card.</p>

Supports default Magento payment methods:

+ Authorize.net
+ Saved CC

May be able to dynamically support other credit card gateways/extensions out of the box if they don't compete for the credit card forms (i.e., form templates located in `app/design/frontend/base/default/template/payment/form`).

----------------

## Dependencies

+ Ash_Core
+ Ash_Jquery
+ Mage_Payment

## Module-related files outside of this directory

+ app/etc/modules/Ash_ValidateCreditCard.xml
+ app/design/frontend/base/default/layout/ash_validatecreditcard.xml
+ app/design/frontend/base/default/template/ash_validatecreditcard
+ skin/frontend/base/default/ash_validatecreditcard/css/
+ skin/frontend/base/default/ash_validatecreditcard/images/
+ skin/frontend/base/default/ash_validatecreditcard/js/
