<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core data helper
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'ash_devbar/general/enabled';

    /**
     * Use included Zurb
     *
     * @var string
     */
    const XML_PATH_USE_INCLUDED_ZURB = 'ash_devbar/general/use_included_zurb';

    /**
     * Disabled Output
     *
     * @var string
     */
    const XML_PATH_DISABLED_OUTPUT = 'advanced/modules_disable_output/ash_devbar';

    /**
     * Check if extension and dependencies are enabled
     *
     * @return  bool
     */
    public function isEnabled()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)
            && !$this->isOutputDisabled()
            && $this->areDependenciesMet()) {
            return true;
        }

        return false;
    }

    /**
     * Check if output has been disabled
     *
     * @return  bool
     */
    public function isOutputDisabled()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_DISABLED_OUTPUT)) {
            return true;
        }

        return false;
    }

    /**
     * Check if required dependencies are enabled
     *
     * @return  bool
     */
    public function areDependenciesMet()
    {
        if (Mage::helper('ash_jquery')->isEnabled()
            && Mage::helper('ash_jquery')->isUiEnabled()
            && Mage::helper('ash_fontawesome')->isEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the included Zurb Foundation should be used
     *
     * @return  bool
     */
    public function useIncludedZurb()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_USE_INCLUDED_ZURB)) {
            return true;
        }

        return false;
    }

    /**
     * Formats numbers as bytes
     *
     * @param   integer $bytes
     * @return  string
     */
    static public function formatBytes($bytes)
    {
        $size = $bytes / 1024;
        if ($size < 1024) {
            $size  = number_format($size, 2);
            $size .= 'KB';
        } else  {
            if ($size / 1024 < 1024)  {
                $size  = number_format($size / 1024, 2);
                $size .= 'MB';
            }
            else if ($size / 1024 / 1024 < 1024) {
                $size  = number_format($size / 1024 / 1024, 2);
                $size .= 'GB';
            }
        }

        return $size;
    }

    /**
     * Formats time to specified decimal place
     *
     * @param   float $number
     * @return  float
     */
    static public function formatNumber($number, $decimal=6)
    {
        return number_format($number, $decimal);
    }
}
