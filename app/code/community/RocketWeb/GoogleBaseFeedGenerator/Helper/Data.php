<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_GoogleBaseFeedGenerator
 * @copyright Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_GoogleBaseFeedGenerator_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Checks if a module is enabled or not
     * @param $module_namespace
     * @return bool
     */
    public function isModuleEnabled($module_namespace = null)
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        return isset($modulesArray[$module_namespace]) && $modulesArray[$module_namespace]->active == "true";
    }

    /**
     * $string = preg_replace_callback('/\\\\u(\w{4})/', array(Mage::helper('googlebasefeedgenerator'), 'jsonUnescapedUnicodeCallback'), $string);
     * php 5.2 alternative to JSON_UNESCAPED_UNICODE
     *
     * @param $matches
     * @return string
     */
    public function jsonUnescapedUnicodeCallback($matches) {
        return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
    }

    /**
     * if (extension_loaded('mbstring')) {
     *    $string = preg_replace_callback("/(&#?[a-z0-9]{2,8};)/i", array(Mage::helper('googlebasefeedgenerator'), 'htmlEntitiesToUtf8Callback'), $string);
     * }
     *
     * @param $matches
     * @return string
     */
    public function htmlEntitiesToUtf8Callback($matches) {
        return mb_convert_encoding($matches[1], "UTF-8", "HTML-ENTITIES");
    }
}