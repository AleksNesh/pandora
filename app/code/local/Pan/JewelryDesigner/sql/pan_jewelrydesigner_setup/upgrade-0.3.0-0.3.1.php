<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

$installer = $this;

$installer->startSetup();

$query = <<<QUERY
ALTER TABLE {$this->getTable('pan_jewelrydesigner/design')} CHANGE `jewelery_type` `jewelry_type` VARCHAR(20) CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT ''  COMMENT 'Example: "bracelet", "watch", "necklace", "earring"';
QUERY;

$installer->run($query);

// end transaction
$installer->endSetup();


