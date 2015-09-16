<?php
/**
 * Ash Up Extension
 *
 * Management interface for keeping Ash core extensions updated.
 *
 * @category    Ash
 * @package     Ash_Up
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Installer Shell Script
 *
 * @category    Ash
 * @package     Ash_Up
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Up_Shell_Installer extends Mage_Shell_Abstract
{
    /**
     * Get extensions colleciton
     *
     * @return Ash_Up_Model_Resource_Extension_Collection
     */
    protected function _getExtensionCollection()
    {
        return Mage::getModel('ash_up/extension')->getCollection();
    }

    /**
     * Helper function to display extensions that are available for installation
     *
     * @return void
     */
    protected function _displayAvailableExtensions()
    {
        echo "\n";
        echo "Available extensions: \n";
        echo "----------------------------------------\n";
        $collection = $this->_getExtensionCollection()->addAvailableFilter();
        $this->_renderExtensionList($collection, 'remote');
    }

    /**
     * Helper function to display extensions that are installed
     *
     * @return void
     */
    protected function _displayInstalledExtensions()
    {
        echo "\n";
        echo "Installed extensions: \n";
        echo "----------------------------------------\n";

        $collection = $this->_getExtensionCollection()->addInstalledFilter();
        $this->_renderExtensionList($collection, 'local');
    }

    /**
     * Helper function to display extensions that are outdated
     *
     * @return void
     */
    protected function _displayOutdatedExtensions()
    {
        echo "\n";
        echo "Outdated extensions: \n";
        echo "----------------------------------------\n";

        $collection = $this->_getExtensionCollection()->addInstalledFilter();
        foreach ($collection as $key => $extension) {
            if (version_compare($extension->getLocalVersion(), $extension->getRemoteVersion(), '>=')) {
                $collection->removeItemByKey($key);
            }
        }
        $this->_renderExtensionList($collection, 'both');
    }

    /**
     * Helper function to display all extensions
     *
     * @return void
     */
    protected function _displayAllExtensions()
    {
        echo "\n";
        echo "All extensions: \n";
        echo "----------------------------------------\n";
        $collection = $this->_getExtensionCollection();
        $this->_renderExtensionList($collection, 'remote');
    }

    /**
     * Render list off passed extensions
     *
     * @param   Mage_Core_Model_Resource_Db_Collection_Abstract $extensions
     * @param   string $verionType
     * @return  void
     */
    protected function _renderExtensionList(Mage_Core_Model_Resource_Db_Collection_Abstract $extensions, $verionType='local')
    {
        foreach ($extensions as $extension) {
            switch ($verionType) {
                case 'both':
                    echo sprintf('%-24s', $extension->getExtensionName());
                    echo $extension->getLocalVersion() . " --> " . $extension->getRemoteVersion() . "\n";
                    break;
                case 'remote':
                    echo sprintf('%-34s', $extension->getExtensionName());
                    echo $extension->getRemoteVersion() . "\n";
                    break;
                case 'local':
                default:
                    echo sprintf('%-34s', $extension->getExtensionName());
                    echo $extension->getLocalVersion() . "\n";
                    break;
            }
        }
    }

    /**
     * Parse string with extensions and return array of extension IDs
     *
     * @param  string $string
     * @return array
     */
    protected function _parseExtensionString($string)
    {
        $extensions = array();
        if (!empty($string)) {
            $names = explode(',', $string);
            foreach ($names as $name) {
                $extension = Mage::getModel('ash_up/extension')->loadByName(trim($name));
                if (!$extension->getId()) {
                    echo 'Warning: Unknown extension with name ' . trim($name) . "\n";
                } else {
                    $extensions[] = $extension->getId();
                }
            }
        }
        return $extensions;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('update')) {
            try {
                Mage::helper('ash_up')->checkForUpdates();
                $this->_displayAvailableExtensions();
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }
        } else if ($this->getArg('list')) {
            $this->_displayAllExtensions();
        } else if ($this->getArg('installed')) {
            $this->_displayInstalledExtensions();
        } else if ($this->getArg('outdated')) {
            $this->_displayOutdatedExtensions();
        } else if ($this->getArg('install')) {

            // look up extension by passed code
            if ($this->getArg('install')) {
                $extensions = $this->_parseExtensionString($this->getArg('install'));

                try {
                    Mage::helper('ash_up')->upgradeExtensions($extensions);
                    echo "\nExtension(s) installed successfully.\n";
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }
            }

        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f up.php -- [options]

  --install <extension>     Install the named extension
  update                    Update extension's list from remote API
  list                      List all available extensions
  installed                 List installed extensions
  outdated                  List oudated extensions
  help                      This help

  <extension>               Comma separated names of extensions

USAGE;
    }
}

$shell = new Ash_Up_Shell_Installer();
$shell->run();
