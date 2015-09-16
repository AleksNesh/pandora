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

/**
 * Core data helper
 *
 * @category    Ash
 * @package     Ash_Up
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Up_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Repository URI
     *
     * @var string
     */
    const XML_PATH_REPO_URI = 'ash_up/general/repo_uri';

    /**
     * Remote API Cache Lifetime
     *
     * @var string
     */
    const XML_PATH_CACHE_LIFETIME = 'ash_up/general/cache_lifetime';

    /**
     * FTP Enabled
     *
     * @var string
     */
    const XML_PATH_FTP_ENABLED = 'ash_up/ftp/enabled';

    /**
     * Process the passed extensions and attempt to install or upgrade
     *
     * @param  array $extensions
     * @return void
     */
    public function upgradeExtensions(array $extensions)
    {
        self::disableTimeLimit();

        // find extensions marked for upgrade
        $collection = Mage::getModel('ash_up/extension')->getCollection()
            ->addFieldToFilter('extension_id', array('in' => $extensions));

        foreach ($collection as $extension) {
            $extension->load($extension->getId());
            $this->downloadArchive($extension)
                 ->installArchive($extension);
        }

        $this->cleanCache();
    }

    /**
     * Parse remote extension repository and update any installed modules with
     * available version information.
     *
     * @return void
     */
    public function checkForUpdates()
    {
        self::disableTimeLimit();
        $results = $this->getAvailableExtensions();

        foreach ($results as $node) {
            $extension = Mage::getModel('ash_up/extension')
                ->loadByName((string)$node->name);

            // update local database record with remote versions
            if ((bool)Mage::getConfig()->getNode("modules/{$extension->getExtensionName()}/active")) {
                $extension->setInstalledFlag(1);
            }

            $extension
                ->load($extension->getId())
                ->setDownloadUri($this->generateAmazonDownloadUri($node))
                ->setLastChecked(time())
                ->setRemoteVersion((string)$node->version->latest)
                ->save();
        }
    }

    /**
     * Connect to remote API at retreive all available extensions. Method caches
     * remote API call.
     *
     * @return array
     */
    public function getAvailableExtensions()
    {
        $cache   = Mage::getSingleton('core/cache');
        $apiUri  = Mage::getStoreConfig(self::XML_PATH_REPO_URI);
        $expires = Mage::getStoreConfig(self::XML_PATH_CACHE_LIFETIME);
        $key     = md5($apiUri);

        // look for a cached result
        $results = $cache->load($key);
        if (!$results) {
            // request fresh results
            try {
                $client   = new Varien_Http_Client($apiUri);
                $response = $client->request(Zend_Http_Client::GET);
                $results  = json_decode($response->getBody());

                if ($response->getStatus() != 200) {
                    Mage::throwException($this->__('Error downloading URI (%s): %s',
                        $uri, $results->message));
                }

                // convert to array if not already
                if (!is_array($results)) {
                    $results = array($results);
                }

                // save in cache
                $cache->save(serialize($results), $key, array(), $expires);
            } catch (Exception $e) {
                Mage::logException($e);
                $results = array();
            }
        } else {
            // return cached results
            $results = unserialize($results);
        }

        return $results;
    }

    /**
     * Given a valid Github repository URI, generate a download URI for the project's
     * default branch.
     *
     * @param  stdClass|string $uri
     * @return string
     */
    public function generateGithubDownloadUri($uri)
    {
        // replace Git protocol
        $uri = str_replace('git://', 'https://', (string)$uri);

        // convert URI to Github's download format
        $uri = str_replace('.git', '/archive/master.zip', $uri);

        return $uri;
    }

    /**
     * Generate an Amazon S3 download URI for the project based on extension name
     * and version.
     *
     * @param  stdClass|string $node
     * @return string
     */
    public function generateAmazonDownloadUri($node)
    {
        $urlPrefix = 'https://s3.amazonaws.com/augustash/mage/';
        $fileName  = strtolower((string)$node->name) . '-'
            . (string)$node->version->latest;

        return $urlPrefix . $fileName . '.zip';
    }

    /**
     * Attempt to download the extension archive file to var directory
     *
     * @param  Ash_Up_Model_Extension $extension
     * @return Ash_Up_Helper_Data
     */
    public function downloadArchive(Ash_Up_Model_Extension $extension)
    {
        $uri  = $extension->getDownloadUri();
        $path = Mage::getConfig()->getVarDir('ash_installer/downloads');
        Mage::getConfig()->createDirIfNotExists($path);

        $archivePath = $path . DIRECTORY_SEPARATOR . $extension->getExtensionName() . '.zip';

        // configure client to download archive file
        $client  = new Zend_Http_Client($uri);
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_BINARYTRANSFER => true,
            )
        ));
        $client->setAdapter($adapter);
        $client->setStream($archivePath);

        // stream file to specified path
        $response = $client->request(Zend_Http_Client::GET);

        if ($response->getStatus() != 200) {
            Mage::throwException($this->__('Error downloading archive (%s): %s',
                $extension->getExtensionName(), $response->getMessage()));
        }

        // set archive path
        $extension
            ->setLastDownloaded(time())
            ->setArchivePath($archivePath)
            ->save();

        return $this;
    }

    /**
     * Attempt to install the extension archive
     *
     * @param  Ash_Up_Model_Extension $extension
     * @return Ash_Up_Helper_Data
     */
    public function installArchive(Ash_Up_Model_Extension $extension)
    {
        if (!$extension->getArchivePath()) {
            Mage::throwException($this->__('Invalid archive path'));
        }

        // install by Zip or FTP
        if (!Mage::getStoreConfigFlag(self::XML_PATH_FTP_ENABLED)) {
            $this->unarchive($extension->getArchivePath(), Mage::getBaseDir());
        } else {
            // @TODO Implement FTP installation
            // $errors = $this->ftpUpload();
            // if ($errors) {
            //     foreach ($errors as $error) {
            //         Mage::log($error, null, 'ash_installer.log');
            //     }
            //     Mage::throwException($this->__('Errors during FTP upload; See ash_installer.log'));
            // }
            Mage::throwException($this->__('FTP installation is not implemented yet'));
        }

        // update extension record
        $extension
            ->setInstalledFlag(1)
            ->save();

        return $this;
    }

    /**
     * Examine the downloaded archive and determine how to unpack the file type
     *
     * @param  string $archive
     * @param  string $target
     * @return Ash_Up_Helper_Data
     */
    public function unarchive($archive, $target)
    {
        switch (strtolower(pathinfo($archive, PATHINFO_EXTENSION))) {
            case 'zip':
                $this->unzip($archive, $target);
                break;
            default:
                Mage::throwException($this->__('Unknown archive format'));
                break;
        }

        return $this;
    }

    /**
     * Unpack the given Zip archive
     *
     * @param  string $archive
     * @param  string $target
     * @return Ash_Up_Helper_Data
     */
    public function unzip($archive, $target)
    {
        // check for required extension
        if (!extension_loaded('zip')) {
            Mage::throwException($this->__('Zip PHP extension is not installed! Cannot install extensions.'));
        }
        $zip = new ZipArchive();

        // open
        if (!$zip->open($archive)) {
            Mage::throwException($this->__('Invalid or corrupted zip archive'));
        }

        // extract
        if (!$zip->extractTo($target)) {
            $zip->close();
            Mage::throwException($this->__('An error occured while unpacking zip archive. Please check destination write permissions: %s', $target));
        }

        // close
        $zip->close();
        return $this;
    }

    /**
     * Clears out Magento and APC cached data. Generally called after an extension
     * is installed or upgraded to make sure new data is processed
     *
     * @return Ash_Up_Helper_Data
     */
    public function cleanCache()
    {
        Mage::app()->cleanCache();
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
            apc_clear_cache('user');
        }

        return $this;
    }

    /**
     * To help deal with the possibility of long-running tasks, disable the PHP
     * execution timeout.
     *
     * @return void
     */
    static public function disableTimeLimit()
    {
        set_time_limit(0);
    }
}
