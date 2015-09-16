<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('lookbookslider')} ADD `effect` TEXT NOT NULL;
ALTER TABLE {$this->getTable('lookbookslider')} ADD `navigation` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE {$this->getTable('lookbookslider')} ADD `navigation_hover` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE {$this->getTable('lookbookslider')} ADD `thumbnails` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE {$this->getTable('lookbookslider')} ADD `time` INT(10) NOT NULL DEFAULT '7000';
ALTER TABLE {$this->getTable('lookbookslider')} ADD `trans_period` INT(10) NOT NULL DEFAULT '1500';

ALTER TABLE {$this->getTable('lookbookslider_slide')} ADD `caption` TEXT NOT NULL;
");

$installer->endSetup(); 