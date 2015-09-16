<?php
/**
 * Simple module for updating system configuration data.
 *
 * @category    Pan
 * @package     Pan_Updater
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * -----------------------------------------------------------------------------
 * GENERAL SETTINGS/CONFIGURATION FROM CUSTOMER WORKSHEET
 * -----------------------------------------------------------------------------
 */

/**
 * ----------------------------
 * General - Store Information
 * ----------------------------
 */
$installer->setConfigData('general/store_information/name', 'Pandora Mall of America');
$installer->setConfigData('general/store_information/phone', '800 878-7868');
$installer->setConfigData('general/store_information/address', "8306 Tamarack Village STE 401\nWoodbury, MN 55125");

/**
 * ----------------------------
 * Design
 * ----------------------------
 */
$installer->setConfigData('design/footer/copyright', 'Pandora Jewelry &copy 2014 | U.S. Pat. #7,007,507');

/**
 * ----------------------------
 * Store Emails
 * ----------------------------
 */
$installer->setConfigData('trans_email/ident_general/name', 'Customer Service');
$installer->setConfigData('trans_email/ident_general/email', 'service@pandoramoa.com');
$installer->setConfigData('trans_email/ident_sales/name', 'Customer Service');
$installer->setConfigData('trans_email/ident_sales/email', 'service@pandoramoa.com');
$installer->setConfigData('trans_email/ident_support/name', 'Customer Support');
$installer->setConfigData('trans_email/ident_support/email', 'support@pandoramoa.com');

/**
 * ----------------------------
 * Contact Us
 * ----------------------------
 */
$installer->setConfigData('contacts/contacts/enabled', 1);
$installer->setConfigData('contacts/email/recipient_email', 'service@pandoramoa.com');
$installer->setConfigData('contacts/email/sender_email_identity', 'general');

/**
 * ----------------------------
 * Catalog - Reviews, Notifications, Email-to-Friend
 * ----------------------------
 */
// reviews by guest
$installer->setConfigData('catalog/review/allow_guest', 0);
// notifications
$installer->setConfigData('catalog/productalert/allow_price', 1);
$installer->setConfigData('catalog/productalert/allow_stock', 1);
$installer->setConfigData('catalog/productalert/email_identity', 'general');
// email to a friend
$installer->setConfigData('sendfriend/email/enabled', 1);
$installer->setConfigData('sendfriend/email/allow_guest', 1);

/**
 * Inventory
 */
$installer->setConfigData('cataloginventory/options/show_out_of_stock', 1);
$installer->setConfigData('cataloginventory/item_options/manage_stock', 1);

/**
 * ----------------------------
 * Customer Accounts
 * ----------------------------
 */
// confirm email upon creation
$installer->setConfigData('customer/create_account/confirm', 0);

// wishlist
$installer->setConfigData('wishlist/general/active', 1);

/**
 * ----------------------------
 * Sales
 * ----------------------------
 */
// sales
$installer->setConfigData('sales/reorder/allow', 0);
$installer->setConfigData('sales/minimum_order/active', 0);
$installer->setConfigData('sales/gift_options/allow_order', 1);
$installer->setConfigData('sales/gift_options/allow_items', 1);
// sales emails (notifications)
$installer->setConfigData('sales_email/order/enabled', 1);
$installer->setConfigData('sales_email/order/copy_to', 'orders@pandoramoa.com,webmaster@pandoramoa.com');

// TAX
$installer->setConfigData('tax/calculation/based_on', 'billing');               # 7.125% for all MN billing addresses

// Checkout
$installer->setConfigData('checkout/options/guest_checkout', 1);
$installer->setConfigData('checkout/options/enable_agreements', 0);
$installer->setConfigData('checkout/cart/redirect_to_cart', 0);

// Shipping
$installer->setConfigData('shipping/origin/country_id', 'US');
$installer->setConfigData('shipping/origin/region_id', 34);                     # 34 => 'Minnesota'
$installer->setConfigData('shipping/origin/postcode', '55125');
$installer->setConfigData('shipping/origin/city', 'Woodbury');
$installer->setConfigData('shipping/origin/street_line1', '8306 Tamarack Village');
$installer->setConfigData('shipping/origin/street_line2', '#401');

/**
 * ----------------------------
 * Google
 * ----------------------------
 */
$installer->setConfigData('google/analytics/active', 0);                        # Needs to be updated when going live
$installer->setConfigData('google/analytics/account', 'UA-177618-4');           # Pulled from current PandoraMOA.com

/**
 * ----------------------------
 * Payment Methods
 * ----------------------------
 */
$installer->setConfigData('payment/account/merchant_country', 'US');

// Disable payment methods (i.e., PayPal, Check/Money Order, Purchase Order)
$installer->setConfigData('payment/checkmo/active', 0);
$installer->setConfigData('payment/ccsave/active', 0);
$installer->setConfigData('payment/purchaseorder/active', 0);
$installer->setConfigData('payment/authorizenet_directpost/active', 0);
$installer->setConfigData('payment/cashondelivery/active', 0);
$installer->setConfigData('payment/banktransfer/active', 0);

// disable PayPal
$installer->setConfigData('payment/required_settings/enable_payflow_advanced', 0);
$installer->setConfigData('payment/wpp_required_settings/enable_wpp', 0);
$installer->setConfigData('payment/wps_required_settings/enable_wps', 0);
$installer->setConfigData('payment/paypal_payflow_required/enable_paypal_payflow', 0);
$installer->setConfigData('payment/payflow_link_required/enable_payflow_link', 0);
$installer->setConfigData('payment/payflow_link_required/enable_express_checkout', 0);
$installer->setConfigData('payment/express_checkout_required/enable_express_checkout', 0);


/**
 * Authorize.Net  - AAI SANDBOX ACCOUNT CREDENTIALS
 */
$installer->setConfigData('payment/authorizenet/active', 1);
$installer->setConfigData('payment/authorizenet/title', 'Credit Card');
$installer->setConfigData('payment/authorizenet/payment_action', 'authorize');
$installer->setConfigData('payment/authorizenet/order_status', 'processing');
$installer->setConfigData('payment/authorizenet/test', 1);

# live cgi_url => 'https://secure.authorize.net/gateway/transact.dll'
$installer->setConfigData('payment/authorizenet/cgi_url', 'https://test.authorize.net/gateway/transact.dll');

$installer->setConfigData('payment/authorizenet/email_customer', 0);
$installer->setConfigData('payment/authorizenet/cctypes', 'AE,VI,MC,DI');
$installer->setConfigData('payment/authorizenet/useccv', 1);
$installer->setConfigData('payment/authorizenet/allowspecific', 0);             # 0 => All Allowed, 1 => Specific



$installer->endSetup();
