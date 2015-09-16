<?php

/**
 * Simple module for custom override of giftcard imports.
 *
 * @category    Pan
 * @package     Pan_Giftcards
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'), 'card_reference', 'varchar(255) NULL');

$this->endSetup();
