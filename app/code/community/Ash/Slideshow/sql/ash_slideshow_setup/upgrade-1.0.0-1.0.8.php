<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

$installer = $this;

// start transaction
$installer->startSetup();

$sqlStatement = <<<STATEMENT
ALTER TABLE {$this->getTable('ash_slideshow/asset')} ADD `use_modal` TINYINT(1) NULL DEFAULT '0' COMMENT 'Display slide content (description) in a modal window' AFTER `link_url`;
STATEMENT;

$installer->run($sqlStatement);

// end transaction
$installer->endSetup();
