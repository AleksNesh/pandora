<?php
/**
 * Ash Core Extension
 *
 * Maintains common settings and configuration for AAI-built Magento websites.
 *
 * @category    Ash
 * @package     Ash_Core
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML Head block
 *
 * @category    Ash
 * @package     Ash_Core
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Core_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    /**
     * Add HEAD External Item
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param  string $type
     * @param  string $name
     * @param  string $params
     * @param  string $if
     * @param  string $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addExternalItem($type, $name, $params=null, $if=null, $cond=null)
    {
        parent::addItem($type, $name, $params=null, $if=null, $cond=null);
    }

    /**
     * Remove External Item from HEAD entity
     *
     * @param  string $type
     * @param  string $name
     * @return Mage_Page_Block_Html_Head
     */
    public function removeExternalItem($type, $name)
    {
        parent::removeItem($type, $name);
    }

    /**
     * Classify HTML head item and queue it into "lines" array
     *
     * @see    self::getCssJsHtml()
     * @param  array &$lines
     * @param  string $itemIf
     * @param  string $itemType
     * @param  string $itemParams
     * @param  string $itemName
     * @param  array $itemThe
     * @return void
     */
    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe)
    {
        $params = $itemParams ? ' ' . $itemParams : '';
        $href   = $itemName;
        switch ($itemType) {
            case 'rss':
                $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;

            case 'external_js':
                $lines[$itemIf]['other'][] = sprintf('<script type="text/javascript" src="%s" %s></script>', $href, $params);
                break;

            case 'external_css':
                $lines[$itemIf]['other'][] = sprintf('<link rel="stylesheet" type="text/css" href="%s" %s/>', $href, $params);
                break;
        }
    }
}
