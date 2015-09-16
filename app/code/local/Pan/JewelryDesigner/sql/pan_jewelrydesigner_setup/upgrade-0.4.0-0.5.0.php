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
ALTER TABLE {$this->getTable('pan_jewelrydesigner/design')} ADD `wishlist_count` INT(11) NULL DEFAULT 0 AFTER `times_cloned`;
ALTER TABLE {$this->getTable('pan_jewelrydesigner/design')} ADD `add_to_cart_count` INT(11) NULL DEFAULT 0 AFTER `wishlist_count`;
QUERY;

$installer->run($query);

// end transaction
$installer->endSetup();
