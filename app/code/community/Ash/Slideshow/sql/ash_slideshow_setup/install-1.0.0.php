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

$installer->startSetup();

$createAshSlideshowAssetsTableSql = <<<ASH_SLIDESHOW_ASSETS_TABLE_SQL
-- DROP TABLE IF EXISTS {$this->getTable('ash_slideshow/asset')};
CREATE TABLE IF NOT EXISTS {$this->getTable('ash_slideshow/asset')} (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Status of this asset 1/0 enabled/disabled",
`title` VARCHAR(255) NOT NULL COMMENT "The asset's main title.",
`subtitle` VARCHAR(255) NULL COMMENT "The asset's  subtitle.",
`description` TEXT NULL COMMENT 'This field is used as caption for slides.',
`image` VARCHAR(255) NULL COMMENT "The image to be used for the slideshow.",
`link_text` VARCHAR(255) NULL COMMENT "The text to be used for the button or link.",
`link_url` VARCHAR(255) NULL COMMENT "The link to be used when clicked on slide asset.",
`created_at` DATETIME NULL COMMENT "Time of creation",
`updated_at` DATETIME NULL COMMENT "Time of last update",
PRIMARY KEY (`id`)
) engine=InnoDB default charset=utf8;
ASH_SLIDESHOW_ASSETS_TABLE_SQL;

$createAshSlideshowSlideshowAssetsTableSql = <<<ASH_SLIDESHOW_SLIDESHOW_ASSETS_TABLE_SQL
-- DROP TABLE IF EXISTS {$this->getTable('ash_slideshow/slideshowasset')};
CREATE TABLE IF NOT EXISTS {$this->getTable('ash_slideshow/slideshowasset')} (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`slide_id` INT(11) UNSIGNED NOT NULL COMMENT "Slideshow's Id",
`asset_id` INT(11) UNSIGNED NOT NULL COMMENT "Asset's Id",
`asorder` INT(11) UNSIGNED NOT NULL COMMENT "Slide Order",
`created_at` DATETIME NULL COMMENT "Time of creation",
`updated_at` DATETIME NULL COMMENT "Time of last update",
PRIMARY KEY (`id`)
) engine=InnoDB default charset=utf8;
ASH_SLIDESHOW_SLIDESHOW_ASSETS_TABLE_SQL;

$createAshSlideshowSlideshowsTableSql = <<<ASH_SLIDESHOW_SLIDESHOWS_TABLE_SQL
-- DROP TABLE IF EXISTS {$this->getTable('ash_slideshow/slideshow')};
CREATE TABLE IF NOT EXISTS {$this->getTable('ash_slideshow/slideshow')} (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`status` TINYINT(1) NOT NULL DEFAULT '0',
`slideshow_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT "Slideshow's Title",
`layout` VARCHAR(255) NOT NULL DEFAULT 'default',
`mode` VARCHAR(255) NOT NULL DEFAULT 'horizontal' COMMENT 'Type of transition between slides (horizontal, vertical, fade)',
`speed` INT(11) NOT NULL DEFAULT 500 COMMENT 'Slide transition duration (in ms)',
`slide_margin` INT(11) NOT NULL DEFAULT 0 COMMENT 'Margin between each slide',
`start_slide` INT(11) NOT NULL DEFAULT 0 COMMENT 'Starting slide index (zero-based)',
`random_start` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '(Boolean) Start slider on a random slide',
`slide_selector` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Element to use as slides (ex. "div.slide").',
`infinite_loop` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '(Boolean) If true, clicking "Next" while on the last slide will transition to the first slide and vice-versa',
`hide_control_on_end` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If true, "Next" control will be hidden on last slide and vice-versa. Note: Only used when infiniteLoop: false',
`easing` VARCHAR(255) DEFAULT NULL COMMENT "The type of 'easing' to use during transitions. If using CSS transitions, include a value for the transition-timing-function property. If not using CSS transitions, you may include plugins/jquery.easing.1.3.js for many options. See http://gsgd.co.uk/sandbox/jquery/easing/ for more info. If using CSS: 'linear', 'ease', 'ease-in', 'ease-out', 'ease-in-out', 'cubic-bezier(n,n,n,n)'. If not using CSS: 'swing', 'linear' (see the above file for more options)",
`captions` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "(Boolean) Include image captions. Captions are derived from the image's 'title' attribute",
`ticker` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "(Boolean) Use slider in ticker mode (similar to a news ticker)",
`ticker_hover` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "(Boolean) Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!",
`adaptive_height` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "(Boolean) Dynamically adjust slider height based on each slide's height",
`adaptive_height_speed` INT(11) NOT NULL DEFAULT 500 COMMENT "Slide height transition duration (in ms). Note: only used if adaptiveHeight: true",
`video` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "(Boolean) If any slides contain video, set this to true. Also, include plugins/jquery.fitvids.js See http://fitvidsjs.com/ for more info",
`responsive` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) Enable or disable auto resize of the slider. Useful if you need to use fixed width sliders.",
`use_css` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, CSS transitions will be used for horizontal and vertical slide animations (this uses native hardware acceleration). If false, jQuery animate() will be used.",
`preload_images` VARCHAR(255) NOT NULL DEFAULT 'visible' COMMENT "If 'all', preloads all images before starting the slider. If 'visible', preloads only images in the initially visible slides before starting the slider (tip: use 'visible' if all slides are identical dimensions)",
`touch_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, slider will allow touch swipe transitions",
`swipe_threshold` INT(11) NOT NULL DEFAULT 50 COMMENT "Amount of pixels a touch swipe needs to exceed in order to execute a slide transition. Note: only used if touchEnabled: true",
`one_to_one_touch` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, non-fade slides follow the finger as it swipes",
`prevent_default_swipe_x` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, touch screen will not move along the x-axis as the finger swipes",
`prevent_default_swipe_y` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, touch screen will not move along the y-axis as the finger swipes",
`pager` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, a pager will be added",
`pager_type` VARCHAR(255) NOT NULL DEFAULT 'full' COMMENT "If 'full', a pager link will be generated for each slide. If 'short', a x / y pager will be used (ex. 1 / 5)",
`pager_short_separator` VARCHAR(10) NOT NULL DEFAULT ' / ' COMMENT "If pagerType: 'short', pager will use this value as the separating character",
`pager_selector` VARCHAR(255) DEFAULT '' COMMENT "Element used to populate the pager. By default, the pager is appended to the bx-viewport (use a jQuery selector)",
`controls` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "(Boolean) If true, 'Next' / 'Prev' controls will be added",
`next_text` VARCHAR(255) NOT NULL DEFAULT 'Next' COMMENT 'Text to be used for the "Next" control',
`prev_text` VARCHAR(255) NOT NULL DEFAULT 'Prev' COMMENT 'Text to be used for the "Prev" control',
`next_selector` VARCHAR(255) NOT NULL DEFAULT 'Next' COMMENT 'Element used to populate the "Next" control',
`prev_selector` VARCHAR(255) NOT NULL DEFAULT 'Prev' COMMENT 'Element used to populate the "Prev" control',
`auto_controls` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '(Boolean) If true, "Start" / "Stop" controls will be added',
`start_text` VARCHAR(255) NOT NULL DEFAULT 'Start' COMMENT 'Text to be used for the "Start" control',
`stop_text` VARCHAR(255) NOT NULL DEFAULT 'Stop' COMMENT 'Text to be used for the "Stop" control',
`auto_controls_combine` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'When slideshow is playing only "Stop" control is displayed and vice-versa',
`auto_controls_selector` VARCHAR(255) DEFAULT NULL COMMENT 'Element used to populate the auto controls (use a jQuery selector)',
`auto` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '(Boolean) Slides will automatically transition',
`pause` INT(11) NOT NULL DEFAULT 4000 COMMENT 'The amount of time (in ms) between each auto transition',
`auto_start` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '(Boolean) Slide show starts playing on load. If false, slideshow will start when the "Start" control is clicked',
`auto_direction` VARCHAR(255) NOT NULL DEFAULT 'next' COMMENT "The direction of auto show slide transitions. Options: 'next', 'prev'",
`auto_hover` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '(Boolean) Slide show will pause when mouse hovers over slider',
`auto_delay` INT(11) NOT NULL DEFAULT 0 COMMENT 'Time (in ms) slide show should wait before starting.',
`min_slides` INT(11) NOT NULL DEFAULT 1 COMMENT 'The minimum number of slides to be shown. Slides will be sized down if carousel becomes smaller than the original size.',
`max_slides` INT(11) NOT NULL DEFAULT 1 COMMENT 'The maximum number of slides to be shown. Slides will be sized up if carousel becomes larger than the original size',
`move_slides` INT(11) NOT NULL DEFAULT 0 COMMENT 'The number of slides to move on transition. This value must be >= minSlides, and <= maxSlides. If zero (default), the number of fully-visible slides will be used.',
`slide_width` INT(11) NOT NULL DEFAULT 0 COMMENT 'The width of each slide. This setting is required for all horizontal carousels!',
`created_at` DATETIME NULL,
`updated_at` DATETIME NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ASH_SLIDESHOW_SLIDESHOWS_TABLE_SQL;

$installer->run($createAshSlideshowSlideshowsTableSql);
$installer->run($createAshSlideshowAssetsTableSql);
$installer->run($createAshSlideshowSlideshowAssetsTableSql);

// end transaction
$installer->endSetup();
