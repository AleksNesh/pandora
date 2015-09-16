<?php
/**
 * Font Awesome icon support
 *
 * @category    Ash
 * @package     Ash_Fontawesome
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core data helper
 *
 * @category    Ash
 * @package     Ash_Fontawesome
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Fontawesome_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED      = 'ash_fontawesome/general/enabled';
    const XML_PATH_USE_CDN      = 'ash_fontawesome/general/use_cdn';
    const XML_PATH_USE_MINIFIED = 'ash_fontawesome/general/use_minified';
    const XML_PATH_VERSION      = 'ash_fontawesome/version/fontawesome';

    /**
     * Check if CDN should be used
     *
     * @return  bool
     */
    public function useCdn()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CDN);
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
     * Check if Font Awesome is enabled
     *
     * @return  boolean
     */
    public function isEnabled()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            return true;
        }

        return false;
    }

    /**
     * Get selected version of Font Awesome
     *
     * @return  string
     */
    public function getVersion()
    {
        return Mage::getStoreConfig(self::XML_PATH_VERSION);
    }

    /**
     * Get URL for Font Awesome
     *
     * @return  string
     */
    public function getFontAwesomeUrl()
    {
        if ($this->useCdn()) {
            $uri = "//netdna.bootstrapcdn.com/font-awesome/%s/css/font-awesome%s";
        } else {
            $uri = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)
                . 'frontend/base/default/ash_fontawesome/%s/css/font-awesome%s';
        }

        return sprintf($uri, $this->getVersion(), $this->_getFileExtension());
    }

    /**
     * Return correct file extension
     *
     * @return  string
     */
    protected function _getFileExtension()
    {
        return ($this->useMinified()) ? '.min.css' : '.css';
    }
}
