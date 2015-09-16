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
 * ----------------------------
 * Shopper Theme configuration
 *
 * TODO: move colors to CSS
 * ----------------------------
 */

// common values
$themeColor     = '#a8a7d7';             // pale purple
$lighterPurple  = $themeColor;           // pale purple
$lightPurple    = '#9999cc';             // light purple
$purple         = '#666699';             // medium/dark purple
$linkColor      = '#656699';             // medium/dark purple
$linkHoverColor = $themeColor;           // medium/dark purple

$transparent    = 'rgba(0, 0, 0, 0)';    // transparent
$white          = '#ffffff';             // white
$black          = '#373230';             // dark black/grey
$grey           = '#767676';
$gray           = $grey;

$contentBg      = '#fafafa';             // smoke/white

/**
 * APPEARANCE PANE
 */
$installer->setConfigData('shoppersettings/appearance/enable_font', 0);
$installer->setConfigData('shoppersettings/appearance/font', 'Rosario');
$installer->setConfigData('shoppersettings/appearance/color', $themeColor);
$installer->setConfigData('shoppersettings/appearance/toolbar_bg', $transparent);
$installer->setConfigData('shoppersettings/appearance/toolbar_color', $purple);
$installer->setConfigData('shoppersettings/appearance/header_bg', $white);
$installer->setConfigData('shoppersettings/appearance/menu_text_color', $black);
$installer->setConfigData('shoppersettings/appearance/slideshow_bg', $lightPurple);
$installer->setConfigData('shoppersettings/appearance/page_title_bg', $themeColor);
$installer->setConfigData('shoppersettings/appearance/content_bg', $contentBg);
$installer->setConfigData('shoppersettings/appearance/content_bg_img_mode', 'stretch'); // 'stretch' or 'tile'
$installer->setConfigData('shoppersettings/appearance/content_link', $linkColor);
$installer->setConfigData('shoppersettings/appearance/content_link_hover', $themeColor);
$installer->setConfigData('shoppersettings/appearance/slider_bg', '#ededed');
$installer->setConfigData('shoppersettings/appearance/slider_border', '#e1e1e1');
$installer->setConfigData('shoppersettings/appearance/footer_bg', '#e3e3e3');
$installer->setConfigData('shoppersettings/appearance/footer_color', $grey);
$installer->setConfigData('shoppersettings/appearance/footer_banners_bg', '#f7f7f6');
$installer->setConfigData('shoppersettings/appearance/footer_info_bg', $white);
$installer->setConfigData('shoppersettings/appearance/footer_info_border', '#ececea');
$installer->setConfigData('shoppersettings/appearance/footer_info_color', $grey);
$installer->setConfigData('shoppersettings/appearance/price_font', 'Open Sans');
$installer->setConfigData('shoppersettings/appearance/timeline', '#322c29');

/**
 * HEADER PANE
 */
$installer->setConfigData('shoppersettings/header/top_signup', 1);
$installer->setConfigData('shoppersettings/header/top_custom_link', 0);

/**
 * DESIGN PANE
 */
$installer->setConfigData('shoppersettings/design/responsive', 1);
$installer->setConfigData('shoppersettings/design/price_circle', 1);
$installer->setConfigData('shoppersettings/design/fixed_header', 1);
$installer->setConfigData('shoppersettings/design/top_compare', 1);
$installer->setConfigData('shoppersettings/header/search_field', 0);
$installer->setConfigData('shoppersettings/header/below_logo', 0);
$installer->setConfigData('shoppersettings/header/override_css', 0);
$installer->setConfigData('shoppersettings/header/hide_compare', 0);
$installer->setConfigData('shoppersettings/header/prev_next', 0);

/**
 * NAVIGATION PANE
 */
$installer->setConfigData('shoppersettings/navigation/use_wide_navigation', 0);
$installer->setConfigData('shoppersettings/navigation/use_navigation', 1);
$installer->setConfigData('shoppersettings/navigation/column_items', 12);
$installer->setConfigData('shoppersettings/navigation/home', 1);
$installer->setConfigData('shoppersettings/navigation/custom_block_width', 600);

/**
 * SOCIAL PANE
 */
$installer->setConfigData('shoppersettings/social/twitter', '');
$installer->setConfigData('shoppersettings/social/tweets_num', '');
$installer->setConfigData('shoppersettings/social/facebook', 'PandoraMallOfAmerica');


$installer->endSetup();
