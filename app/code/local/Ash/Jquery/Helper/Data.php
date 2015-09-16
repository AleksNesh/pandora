<?php
/**
 * Add Jquery/Jquery UI support
 *
 * @category    Ash
 * @package     Ash_Jquery
 * @copyright   Copyright (c) 2013 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core data helper
 *
 * @category    Ash
 * @package     Ash_Jquery
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Jquery_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_USE_MINIFIED               = 'ash_jquery/general/use_minified';

    const XML_PATH_CDN_ENABLED                = 'ash_jquery/cdn/enabled';
    const XML_PATH_CDN_JQUERY_ENABLED         = 'ash_jquery/cdn/jquery_enabled';
    const XML_PATH_CDN_JQUERY_MIGRATE_ENABLED = 'ash_jquery/cdn/jquery_migrate_enabled';
    const XML_PATH_CDN_JQUERYUI_ENABLED       = 'ash_jquery/cdn/jqueryui_enabled';

    const XML_PATH_JQUERY_ENABLED             = 'ash_jquery/jquery/enabled';
    const XML_PATH_JQUERY_MIGRATE_ENABLED     = 'ash_jquery/jquery/migrate_enabled';
    const XML_PATH_JQUERYUI_ENABLED           = 'ash_jquery/jquery/ui_enabled';

    const XML_PATH_JQUERY_VERSION             = 'ash_jquery/version/jquery';
    const XML_PATH_JQUERYUI_VERSION           = 'ash_jquery/version/jquery_ui';

    /**
     * Check if CDN should be used
     *
     * @return  bool
     */
    public function useCdn()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CDN_ENABLED);
    }

    /**
     * Check if minified source should be used
     *
     * @return  bool
     */
    public function useMinified()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_MINIFIED);
    }

    /**
     * Check if jQuery is enabled
     *
     * @return  boolean
     */
    public function isEnabled()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_JQUERY_ENABLED)
            || (Mage::getStoreConfigFlag(self::XML_PATH_CDN_ENABLED)
                && Mage::getStoreConfigFlag(self::XML_PATH_CDN_JQUERY_ENABLED))) {
            return true;
        }

        return false;
    }

    /**
     * Check if jQuery UI is enabled
     *
     * @return  boolean
     */
    public function isUiEnabled()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_JQUERYUI_ENABLED)
            || (Mage::getStoreConfigFlag(self::XML_PATH_CDN_ENABLED)
                && Mage::getStoreConfigFlag(self::XML_PATH_CDN_JQUERYUI_ENABLED))) {
            return true;
        }

        return false;
    }

    /**
     * Check if jQuery is enabled
     *
     * @param   mixed $cdn
     * @return  boolean
     */
    public function isJqueryEnabled($cdn=false)
    {
        if ($cdn !== false) {
            return Mage::getStoreConfigFlag(self::XML_PATH_CDN_JQUERY_ENABLED);
        }

        return Mage::getStoreConfigFlag(self::XML_PATH_JQUERY_ENABLED);
    }

    /**
     * Check if jQuery Migrate is enabled
     *
     * @param   mixed $cdn
     * @return  boolean
     */
    public function isJqueryMigrateEnabled($cdn=false)
    {
        if ($cdn !== false) {
            return Mage::getStoreConfigFlag(self::XML_PATH_CDN_JQUERY_MIGRATE_ENABLED);
        }

        return Mage::getStoreConfigFlag(self::XML_PATH_JQUERY_MIGRATE_ENABLED);
    }

    /**
     * Check if jQuery UI is enabled
     *
     * @param   mixed $cdn
     * @return  boolean
     */
    public function isJqueryUiEnabled($cdn=false)
    {
        if ($cdn !== false) {
            return Mage::getStoreConfigFlag(self::XML_PATH_CDN_JQUERYUI_ENABLED);
        }

        return Mage::getStoreConfigFlag(self::XML_PATH_JQUERYUI_ENABLED);
    }

    /**
     * Get selected version of jQuery
     *
     * @return  string
     */
    public function getJqueryVersion()
    {
        return Mage::getStoreConfig(self::XML_PATH_JQUERY_VERSION);
    }

    /**
     * Get selected version of jQuery Migrate
     *
     * @return  string
     */
    public function getJqueryMigrateVersion()
    {
        return '1.2.1';
    }

    /**
     * Get selected version of jQuery UI
     *
     * @return  string
     */
    public function getJqueryUiVersion()
    {
        return Mage::getStoreConfig(self::XML_PATH_JQUERYUI_VERSION);
    }

    /**
     * Get local source URL for jQuery
     *
     * @return  string
     */
    public function getJqueryUrl()
    {
        return $this->getLocalSourceUrl('jquery');
    }

    /**
     * Get local source URL for jQuery Migrate
     *
     * @return  string
     */
    public function getJqueryMigrateUrl()
    {
        return $this->getLocalSourceUrl('migrate');
    }

    /**
     * Get local source URL for jQuery UI
     *
     * @return  string
     */
    public function getJqueryUiUrl()
    {
        return $this->getLocalSourceUrl('jqueryui');
    }

    /**
     * Get local source path
     *
     * @param   string $type
     * @return  string
     */
    public function getLocalSourceUrl($type)
    {
        switch($type) {
            case 'jquery':
                $file = 'jquery/' . $this->getJqueryVersion() . '/jquery' . $this->_getFileExtension();
                break;
            case 'migrate':
                $file = 'jquery/jquery-migrate-' . $this->getJqueryMigrateVersion() . $this->_getFileExtension();
                break;
            case 'jqueryui':
                $file = 'jquery/' . $this->getJqueryUiVersion() . '/jquery-ui' . $this->_getFileExtension();
                break;
        }

        return $file;
    }

    /**
     * Return correct local file extension
     *
     * @return  string
     */
    protected function _getFileExtension()
    {
        return ($this->useMinified()) ? '.min.js' : '.js';
    }
}
