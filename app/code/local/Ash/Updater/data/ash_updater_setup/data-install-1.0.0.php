<?php
/**
 * Simple module for synchronizing system configuration and database changes.
 *
 * @category    Ash
 * @package     Ash_Updater
 * @copyright   Copyright (c) 2013 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/**
 * General -> General
 */
$installer->setConfigData('general/locale/code', 'en_US');
$installer->setConfigData('general/locale/timezone', 'America/Chicago');

/**
 * General -> Web
 */
$installer->setConfigData('web/seo/use_rewrites', 1);
$installer->setConfigData('web/secure/use_in_frontend', 1);
$installer->setConfigData('web/secure/use_in_adminhtml', 1);

/**
 * General -> Design
 */
$installer->setConfigData('design/footer/copyright', '&copy; <YEAR/> All Rights Reserved');

/**
 * General -> Contacts
 */
$installer->setConfigData('contacts/contacts/enabled', 1);

/**
 * General -> Content Management (Enterprise Only)
 */
$installer->setConfigData('cms/content/versioning', 1);

/**
 * Catalog -> Catalog
 */
$installer->setConfigData('catalog/frontend/list_mode', 'grid');
$installer->setConfigData('catalog/frontend/flat_catalog_category', 1);
$installer->setConfigData('catalog/frontend/flat_catalog_product', 1);
$installer->setConfigData('catalog/review/allow_guest', 0);
$installer->setConfigData('catalog/seo/category_canonical_tag', 1);
$installer->setConfigData('catalog/seo/product_canonical_tag', 1);

/**
 * Catalog -> Inventory
 */
$installer->setConfigData('cataloginventory/item_options/manage_stock', 0);

/**
 * Catalog -> Google Sitemap
 */
$installer->setConfigData('sitemap/generate/enabled', 1);

/**
 * Customers -> Wishlist
 */
$installer->setConfigData('wishlist/general/active', 1);

/**
 * Sales -> Sales
 */
$installer->setConfigData('sales/reorder/allow', 1);

/**
 * Sales -> Checkout
 */
$installer->setConfigData('checkout/options/onepage_checkout_enabled', 1);
$installer->setConfigData('checkout/options/guest_checkout', 1);
$installer->setConfigData('checkout/options/enable_agreements', 0);

/**
 * Sales -> Shipping Settings
 */
$installer->setConfigData('shipping/origin/country_id', 'US');
$installer->setConfigData('shipping/origin/region_id', 34);
$installer->setConfigData('shipping/origin/city', 'Minneapolis');
$installer->setConfigData('shipping/origin/postcode', '55425');
$installer->setConfigData('shipping/option/checkout_multiple', 0);

/**
 * Sales -> Shipping Methods
 */
$installer->setConfigData('carriers/flatrate/active', 1);

/**
 * Sales -> Payment Methods
 */
$installer->setConfigData('payment/checkmo/active', 1);
$installer->setConfigData('payment/ccsave/active', 0);
$installer->setConfigData('payment/free/active', 0);
$installer->setConfigData('payment/purchaseorder/active', 0);

/**
 * Sales -> Gift Cards (Enterprise only)
 */
$installer->setConfigData('giftcard/general/is_redeemable', 0);

/**
 * Advanced -> Admin
 */
$installer->setConfigData('admin/security/session_cookie_lifetime', 3600);

/**
 * Advanced -> System
 */
$installer->setConfigData('system/log/enabled', 1);
$installer->setConfigData('system/log/clean_after_day', 31);
$installer->setConfigData('system/external_page_cache/enabled', 1);
$installer->setConfigData('system/external_page_cache/cookie_lifetime', 3600);
$installer->setConfigData('system/external_page_cache/control', 'zend_page_cache');

/**
 * Advanced -> Advanced
 */
$installer->setConfigData('advanced/modules_disable_output/Mage_Review', 1);
$installer->setConfigData('advanced/modules_disable_output/Mage_Poll', 1);
$installer->setConfigData('advanced/modules_disable_output/Mage_Tag', 1);

/**
 * Advanced -> Developer
 */
$installer->setConfigData('dev/template/allow_symlink', 1);
$installer->setConfigData('dev/log/active', 1);
