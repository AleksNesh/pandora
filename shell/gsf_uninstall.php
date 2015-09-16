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
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */
require_once 'abstract.php';

const SP = DIRECTORY_SEPARATOR;
$paths = array(
    'app' . SP . 'code' . SP . 'community' . SP . 'RocketWeb' . SP . 'GoogleBaseFeedGenerator' . SP,
    'app' . SP . 'design' . SP . 'adminhtml' . SP . 'default' . SP . 'default' . SP . 'template' . SP . 'googlebasefeedgenerator' . SP,
    'app' . SP . 'design' . SP . 'frontend' . SP . 'base' . SP . 'default' . SP . 'layout' . SP . 'rocketweb_googlebasefeedgenerator.xml',
    'app' . SP . 'design' . SP . 'frontend' . SP . 'default' . SP . 'default' . SP . 'layout' . SP . 'rocketweb_googlebasefeedgenerator.xml',
    'app' . SP . 'etc' . SP . 'modules' . SP . 'RocketWeb_GoogleBaseFeedGenerator.xml',
    'app' . SP . 'locale' . SP . 'en_US' . SP . 'RocketWeb_GoogleBaseFeedGenerator.csv',
    'js' . SP . 'rocketweb' . SP . 'googlebasefeedgenerator' . SP,
    'media' . SP . 'feeds' . SP,
    'shell' . SP . 'gen_gbase_feed.php',
    'shell' . SP . 'gsf_generate.php',
    'shell' . SP . 'gsf_uninstall.php'
);

/**
 * Google Shopping Feed Generator Shell Script
 *
 * @category    GoogleBaseFeedGenerator
 * @package     GoogleBaseFeedGenerator_Shell
 */
class GoogleBaseFeedGenerator_Shell_Uninstall extends Mage_Shell_Abstract
{
    private $paths;

    public function setPaths($val)
    {
        $this->paths = $val;
        return $this;
    }

    protected function delete_path($path)
    {
        try {
            if (is_dir($path)) {
                $files = glob($path . '*', GLOB_MARK); // GLOB_MARK adds a slash to directories returned

                foreach ($files as $file) {
                    $this->delete_path($file);
                }
                return rmdir($path);

            } elseif (is_file($path)) {
                return unlink($path);
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteFiles()
    {
        $cnt = 0;

        foreach ($this->paths as $path) {
            $path = $this->_getRootPath() . $path;
            echo "\nRemoving: " . $path . ' ';

            if (!$this->delete_path($path)) {
                echo 'FAILED';
                $cnt++;
            } else {
                echo 'OK';
            }
        }
        return $cnt;
    }

    public function deleteTables()
    {
        $res = Mage::getSingleton('core/resource');
        $con = $res->getConnection('core_write');

        $con->query("DROP TABLE IF EXISTS `{$res->getTableName('rw_gfeed_process')}`");
        $con->query("DROP TABLE IF EXISTS `{$res->getTableName('rw_gfeed_shipping')}`");
        $con->query("DROP TABLE IF EXISTS `{$res->getTableName('rw_gfeed_feed')}`");
        $con->query("DROP TABLE IF EXISTS `{$res->getTableName('rw_gfeed_feed_config_data')}`");
        $con->query("DELETE FROM `{$res->getTableName('core_config_data')}` WHERE `path` LIKE 'rocketweb_googlebasefeedgenerator/%'");
        $con->query("DELETE FROM `{$res->getTableName('core_resource')}` WHERE `code` = 'googlebasefeedgenerator_setup'");
        $con->query("DELETE FROM `{$res->getTableName('eav_attribute')}` WHERE `attribute_code` LIKE 'rw_google_base%'");
        echo "Cleared DB tables and config.\n";
    }

    public function run()
    {
        $this->deleteTables();
        echo $this->deleteFiles() ? "\nSome of the files could not be removed! Please remove them manually.\n" : "\n";
    }
}

$shell = new GoogleBaseFeedGenerator_Shell_Uninstall();
$shell->setPaths($paths)->run();
