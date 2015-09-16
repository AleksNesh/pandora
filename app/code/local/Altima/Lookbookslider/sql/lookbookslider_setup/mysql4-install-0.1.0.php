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

-- DROP TABLE IF EXISTS {$this->getTable('lookbookslider')};
CREATE TABLE {$this->getTable('lookbookslider')} (
  `lookbookslider_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `position` varchar(128) NULL DEFAULT '',
  `width` int(6) unsigned NOT NULL default 0,
  `height` int(6) unsigned NOT NULL default 0,
  `contentbefore` text NOT NULL default '',
  `contentafter` text NOT NULL default '',
  `status` tinyint(1) NULL DEFAULT '1',
  `showslidenames` tinyint(1) NULL DEFAULT '1',
  PRIMARY KEY (`lookbookslider_id`),
  KEY `IDX_LOOKBOOKSLIDER_LOOKBOOKSLIDER_ID` (`lookbookslider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lookbook Slider';

-- DROP TABLE IF EXISTS {$this->getTable('lookbookslider/slide')};
CREATE TABLE {$this->getTable('lookbookslider/slide')} (
  `slide_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `image_path` varchar(255) NOT NULL default '',
  `hotspots` text NOT NULL default '',
  `position` smallint(5) unsigned NOT NULL,
  `status` smallint(6) NOT NULL default '0',
  `lookbookslider_id` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`slide_id`),
  KEY `IDX_LOOKBOOKSLIDER_SLIDE_SLIDE_ID` (`slide_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lookbook Slide';

-- DROP TABLE IF EXISTS `{$this->getTable('lookbookslider/category')}`;
CREATE TABLE `{$this->getTable('lookbookslider/category')}` (
  `lookbookslider_id` smallint(6) NOT NULL,
  `category_id` smallint(6) NOT NULL,
  PRIMARY KEY (`lookbookslider_id`,`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Lookbook Category' ;

-- DROP TABLE IF EXISTS `{$this->getTable('lookbookslider/page')}`;
CREATE TABLE `{$this->getTable('lookbookslider/page')}` (
  `lookbookslider_id` smallint(6) NOT NULL,
  `page_id` smallint(6) NOT NULL,
  PRIMARY KEY (`lookbookslider_id`,`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Lookbook Page' ;

    ");

$installer->setConfigData('lookbookslider/general/hotspot_icon/','default/hotspot-icon.png');

$installer->endSetup(); 