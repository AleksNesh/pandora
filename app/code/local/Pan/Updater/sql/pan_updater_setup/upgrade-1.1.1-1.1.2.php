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
 * --------------------------------------
 * Infomodus UPS Shipping Labels settings
 * --------------------------------------
 */

/**
 * UPS Credentials
 */
$installer->setConfigData('upslabel/credentials/accesslicensenumber', '5CD2E62135276536');
$installer->setConfigData('upslabel/credentials/userid', 'peggy@collectors');
$installer->setConfigData('upslabel/credentials/password', '1859cg');
$installer->setConfigData('upslabel/credentials/shippernumber', '0959X4');

/**
 * Test Mode
 */
$installer->setConfigData('upslabel/testmode/testing', 1); # 1 => 'Yes', 0 => 'No'


/**
 * Package Type Codes
 *
 * 01 => UPS Letter (Envelope)
 * 02 => Customer Supplied Package
 * 03 => Tube
 * 04 => PAK
 * 21 => UPS Express Box
 * 24 => UPS 25KG Box
 * 25 => UPS 10KG Box
 * 30 => Pallet
 * 2a => Small Express Box
 * 2b => Medium Express Box
 * 2c => Large Express Box
 */
$installer->setConfigData('upslabel/packaging/packagingtypecode', '02');
$installer->setConfigData('upslabel/packaging/packagingdescription', 'UPS Envelope');


/**
 * Default Shipping Settings
 */

/**
 * Default Shipping Method for Domestic
 *
 * ""   => Default
 * "01" => UPS Next Day Air
 * "02" => UPS Second Day Air
 * "03" => UPS Ground
 * "12" => UPS Three-Day Select
 * "13" => UPS Next Day Air Saver
 * "14" => UPS Next Day Air Early A.M. SM
 * "59" => UPS Second Day Air A.M.
 * "65" => UPS Saver
 * "07" => UPS Worldwide ExpressSM
 * "08" => UPS Worldwide ExpeditedSM
 * "11" => UPS Standard
 * "54" => UPS Worldwide Express PlusSM
 * "82" => UPS Today StandardSM
 * "83" => UPS Today Dedicated CourrierSM
 * "85" => UPS Today Express
 * "86" => UPS Today Express Saver
 */
$installer->setConfigData('upslabel/shipping/defaultshipmentmethod', '03');
$installer->setConfigData('upslabel/shipping/addtrack', 1);

/**
 * Return Labels
 */
$installer->setConfigData('upslabel/return/frontend_customer_return', 0);
$installer->setConfigData('upslabel/return/refundaccess', 0);
$installer->setConfigData('upslabel/return/default_return', 0);

/**
 * Weight and Dimensions
 */
$installer->setConfigData('upslabel/weightdimension/weightunits', 'LBS');
$installer->setConfigData('upslabel/weightdimension/packweight', '1');
$installer->setConfigData('upslabel/weightdimension/includedimensions', 0);
$installer->setConfigData('upslabel/weightdimension/unitofmeasurement', 'IN');

/**
 * Rates and Payments
 */
$installer->setConfigData('upslabel/ratepayment/negotiatedratesindicator', 1);
$installer->setConfigData('upslabel/ratepayment/invoicelinetotal', 0);

/**
 * Printing Settings
 */
$installer->setConfigData('upslabel/printing/verticalprint', 1);
$installer->setConfigData('upslabel/printing/dimensionx', 288);
$installer->setConfigData('upslabel/printing/dimensiony', 432);
$installer->setConfigData('upslabel/printing/holstx', 288);
$installer->setConfigData('upslabel/printing/holsty', 432);


/**
 * Addresses (Shipper and ShipFrom)
 */
$installer->setConfigData('upslabel/address_1/enable', 1);
$installer->setConfigData('upslabel/address_1/addressname', 'Pandora MOA');
$installer->setConfigData('upslabel/address_1/companyname', 'Pandora MOA');
$installer->setConfigData('upslabel/address_1/attentionname', '');
$installer->setConfigData('upslabel/address_1/phonenumber', '800 878-7868');
$installer->setConfigData('upslabel/address_1/addressline1', '8306 Tamarack Village STE 401');
$installer->setConfigData('upslabel/address_1/city', 'Woodbury');
$installer->setConfigData('upslabel/address_1/stateprovincecode', 'MN');
$installer->setConfigData('upslabel/address_1/postalcode', '55125');
$installer->setConfigData('upslabel/address_1/countrycode', 'US');
$installer->setConfigData('upslabel/address_1/residential', 'N');


$installer->endSetup();
