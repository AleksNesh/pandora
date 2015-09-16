<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core data helper
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'ash_bar/general/enabled';

    /**
     * Allowed IP Addresses
     *
     * @var string
     */
    const XML_PATH_ALLOWED_IPS = 'ash_bar/restrict/allow_ips';

    /**
     * Disabled Output
     *
     * @var string
     */
    const XML_PATH_DISABLED_OUTPUT = 'advanced/modules_disable_output/ash_bar';

    /**
     * Check if Toolbar and jQuery dependency are enabled
     *
     * @return  bool
     */
    static public function isEnabled()
    {
        if (Mage::getStoreConfig(self::XML_PATH_ENABLED)
            && !Mage::getStoreConfig(self::XML_PATH_DISABLED_OUTPUT)
            && Mage::helper('ash_jquery')->isEnabled()
            && Mage::helper('ash_jquery')->isEnabled('jquery_ui')) {
            return true;
        }

        return false;
    }

    /**
     * Formats numbers as bytes
     *
     * @param  integer $bytes
     * @return string
     */
    static public function formatBytes($bytes)
    {
        $size = $bytes / 1024;
        if ($size < 1024) {
            $size  = number_format($size, 2);
            $size .= ' KB';
        } else  {
            if ($size / 1024 < 1024)  {
                $size  = number_format($size / 1024, 2);
                $size .= ' MB';
            }
            else if ($size / 1024 / 1024 < 1024) {
                $size  = number_format($size / 1024 / 1024, 2);
                $size .= ' GB';
            }
        }

        return $size;
    }

    /**
     * Formats time to specified decimal place
     *
     * @param  float $number
     * @return float
     */
    static public function formatNumber($number, $decimal=6)
    {
        return number_format($number, $decimal);
    }

    /**
     * Format SQL keywords with HTML to stand out during display
     *
     * @param  string $sql
     * @return string
     */
    static public function formatSql($sql)
    {
        $sql = preg_replace('/\b(FROM|'
            . 'WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|'
            . 'VALUES)\b/', '<br />\\1', $sql);
        $sql = preg_replace('/\b(UPDATE|SET|SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|'
            . 'WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|'
            . 'DISTINCT|DELETE|INSERT|INTO|VALUES|SHOW|EXPLAIN|AND|OR|CASE|WHEN|'
            . 'IF|THEN|ELSE|IS NULL|IFNULL|SUM|AVG|BETWEEN|MAX|MIN|DATE_FORMAT|'
            . 'EXISTS|ON|CONCAT|END|IS NOT NULL)\b/',
            '<strong class="text-warning">\\1</strong>', $sql);

        return $sql;
    }

    // static public function getMemoryUsage($realUsage = false)
    // {
    //     return memory_get_usage($realUsage);
    // }
}
