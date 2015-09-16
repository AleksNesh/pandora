<?php
/**
 * Core module for providing common functionality between BraceletBuilder and other related submodules
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Pan_JewelryDesigner data helper
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @author      August Ash Team <core@augustash.com>
 */
class Pan_JewelryDesigner_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED                          = 'pan_jewelrydesigner/general/enabled';
    const XML_PATH_ENABLED_IN_ADMIN                 = 'pan_jewelrydesigner/general/enabled_in_admin';
    const ANGULAR_APP_JS_DIR                        = 'js';
    const ANGULAR_APP_CSS_DIR                       = 'css';
    const ANGULAR_APP_IMG_DIR                       = 'img';
    const ANGULAR_APP_VIEWS_DIR                     = 'views';
    const ANGULAR_APP_PARTIALS_DIR                  = 'partials';
    const ANGULAR_APP_BOWER_COMPONENTS_DIR          = 'bower_components';
    const ANGULAR_APP_CONTROLLERS_DIR               = 'controllers';
    const ANGULAR_APP_FILTERS_DIR                   = 'filters';
    const ANGULAR_APP_SERVICES_DIR                  = 'services';
    const ANGULAR_APP_DIRECTIVES_DIR                = 'directives';
    const ANGULAR_APP_PUBLIC_DIR                    = 'public';


    public function inAdminArea()
    {
        return Mage::helper('pan_jewelrydesigner/isadmin')->isAdminArea();
    }

    public function isAdminUser()
    {
        return Mage::helper('pan_jewelrydesigner/isadmin')->isAdminUser();
    }

    public function getAdminUser()
    {
        return Mage::helper('pan_jewelrydesigner/isadmin')->getAdminUser();
    }

    public function isCustomerLoggedIn()
    {
        return Mage::helper('pan_jewelrydesigner/customer')->isCustomerLoggedIn();
    }

    public function getCustomer()
    {
        return Mage::helper('pan_jewelrydesigner/customer')->getCustomer();
    }

    public function camelize($term, $uppercase_first_letter = false)
    {
        return Mage::helper('pan_jewelrydesigner/inflect')->camelize($term, $uppercase_first_letter);
    }

    public function underscore($camel_cased_word)
    {
        return Mage::helper('pan_jewelrydesigner/inflect')->underscore($camel_cased_word);
    }

    public function slugify($text, $glue = '-')
    {
        return Mage::helper('pan_jewelrydesigner/inflect')->slugify($text, $glue);
    }

    public function randomUUID()
    {
        return Mage::helper('pan_jewelrydesigner/inflect')->randomUUID();
    }


    public function getSnapshotDirectoryPath()
    {
        $mediaDir   = Mage::getBaseDir('media');
        $dirPath    =  $mediaDir . DIRECTORY_SEPARATOR . 'pan_jewelrydesigner' . DIRECTORY_SEPARATOR . 'snapshots';

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        return $dirPath;
    }

    public function getSnapshotDirectoryUrl()
    {
        $mediaUrl   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $dirUrl     = $mediaUrl . 'pan_jewelrydesigner' . DIRECTORY_SEPARATOR . 'snapshots';

        return $dirUrl;
    }

    /**
     * Returns a relative url path from the Magento root
     * directory to our module's app directory
     *
     * Example:
     *     echo Mage::helper('pan_jewelrydesigner')->getAngularAppBaseDir();
     *     # app/code/local/Pan/JewelryDesigner/app
     *
     * @return  string
     */
    public function getAngularAppBaseDir()
    {
        $baseDir          = Mage::getBaseDir();
        $moduleDir        = Mage::getModuleDir('Module', 'Pan_JewelryDesigner');
        $angularAppDir    = str_replace($baseDir, '', $moduleDir) . '/app';

        return $angularAppDir;
    }

    /**
     * Returns a local directory relative to our Angular JS app directory
     *
     * @var     string     $directory
     * @return  string
     */
    public function getAngularAppDir($directory = '')
    {
        switch (true) {
            case in_array(strtolower($directory), array('js', 'javascript')):
                $localDir = self::ANGULAR_APP_JS_DIR;
                break;
            case in_array(strtolower($directory), array('css', 'styles', 'stylesheets')):
                $localDir = self::ANGULAR_APP_CSS_DIR;
                break;
            case in_array(strtolower($directory), array('views', '_views', 'templates')):
                $localDir = self::ANGULAR_APP_VIEWS_DIR;
                break;
            case in_array(strtolower($directory), array('partials', '_partials')):
                $localDir = self::ANGULAR_APP_VIEWS_DIR . DIRECTORY_SEPARATOR . self::ANGULAR_APP_PARTIALS_DIR;
                break;
            case in_array(strtolower($directory), array('img', 'images', 'image')):
                $localDir = self::ANGULAR_APP_IMG_DIR;
                break;

            case in_array(strtolower($directory), array('bower_components', 'bower')):
                $localDir = self::ANGULAR_APP_BOWER_COMPONENTS_DIR;
                break;

            case in_array(strtolower($directory), array('public', 'public')):
                $localDir = self::ANGULAR_APP_PUBLIC_DIR;
                break;

            default:
                # DO NOTHING!
                $localDir = $directory;
                break;
        }

        return $this->getAngularAppBaseDir() . DIRECTORY_SEPARATOR . $localDir;
    }

    public function getAngularPublicDir()
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_PUBLIC_DIR);
    }

    public function getAngularPublicCssDir()
    {
        return $this->getAngularPublicDir() . DIRECTORY_SEPARATOR . self::ANGULAR_APP_CSS_DIR;
    }

    public function getAngularPublicJsDir()
    {
        return $this->getAngularPublicDir() . DIRECTORY_SEPARATOR . self::ANGULAR_APP_JS_DIR;
    }

    public function getAngularPublicImgDir()
    {
        return $this->getAngularPublicDir() . DIRECTORY_SEPARATOR . self::ANGULAR_APP_IMG_DIR;
    }

    /**
     * Returns a relative url path (from the Magento root) for the
     * local 'js' directory in our Angular JS app directory
     *
     * @return  string
     */
    public function getAngularAppJsDir()
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_JS_DIR);
    }

    /**
     * Returns a relative url path (from the Magento root) for the
     * local 'css' directory in our Angular JS app directory
     *
     * @return  string
     */
    public function getAngularAppCssDir()
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_CSS_DIR);
    }

    /**
     * Returns a relative url path (from the Magento root) for the
     * local 'css' directory in our Angular JS app directory
     *
     * @return  string
     */
    public function getAngularAppImgDir()
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_IMG_DIR);
    }



    /**
     * Returns a relative url path (from the Magento root) for the
     * local 'views' directory in our Angular JS app directory
     *
     * @return  string
     */
    public function getAngularAppViewsDir()
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_VIEWS_DIR);
    }

    /**
     * Returns a relative url path (from the Magento root) for the
     * local 'views/partials' directory in our Angular JS app directory
     *
     * @return  string
     */
    public function getAngularAppPartialsDir()
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_PARTIALS_DIR);
    }

    public function getAngularAppBowerDir($component = 'angular')
    {
        return $this->getAngularAppDir(self::ANGULAR_APP_BOWER_COMPONENTS_DIR) . DIRECTORY_SEPARATOR . $component;
    }

    public function loadBowerComponent($component = 'angular', $minified = false)
    {
        $bowerDir = $this->getAngularAppBowerDir($component);
        $ext = ($minified) ? '.min.js' : '.js';

        return $bowerDir . DIRECTORY_SEPARATOR . $component . $ext;
    }

    /**
     * Returns relative URL path to a controller's JS file
     *
     * @param  string   $controller # name of the file in the file system, not the 'class' name
     * @param  string   $ext        # i.e., '.js'
     * @return string
     */
    public function getAngularController($controller, $ext = '.js')
    {
        $controllerDir = $this->_getAngularAppJsSubDir(self::ANGULAR_APP_CONTROLLERS_DIR);
        return $controllerDir . DIRECTORY_SEPARATOR . $controller . $ext;
    }

    /**
     * Returns relative URL path to a service's JS file
     *
     * @param  string   $service # name of the file in the file system
     * @param  string   $ext     # i.e., '.js'
     * @return string
     */
    public function getAngularService($service, $ext = '.js')
    {
        $serviceDir = $this->_getAngularAppJsSubDir(self::ANGULAR_APP_SERVICES_DIR);
        return $serviceDir . DIRECTORY_SEPARATOR . $service . $ext;
    }

    /**
     * Returns relative URL path to a filter's JS file
     *
     * @param  string   $filter     # name of the file in the file system, not the 'class' name
     * @param  string   $ext        # i.e., '.js'
     * @return string
     */
    public function getAngularFilter($filter, $ext = '.js')
    {
        $filterDir = $this->_getAngularAppJsSubDir(self::ANGULAR_APP_FILTERS_DIR);
        return $filterDir . DIRECTORY_SEPARATOR . $filter . $ext;
    }

    /**
     * Returns relative URL path to a directive's JS file
     *
     * @param  string   $directive  # name of the file in the file system, not the 'class' name
     * @param  string   $ext        # i.e., '.js'
     * @return string
     */
    public function getAngularDirective($directive, $ext = '.js')
    {
        $directiveDir = $this->_getAngularAppJsSubDir(self::ANGULAR_APP_DIRECTIVES_DIR);
        return $directiveDir . DIRECTORY_SEPARATOR . $directive . $ext;
    }

    /**
     * Return the relative path to the AngularJS app's js/services directory
     *
     * @param  string  $subdir  # e.g., 'controllers', 'filters', 'services', 'directives', etc.
     * @return string
     */
    protected function _getAngularAppJsSubDir($subdir = self::ANGULAR_APP_CONTROLLERS_DIR)
    {
        return $this->getAngularAppJsDir() . DIRECTORY_SEPARATOR . $subdir;
    }

    /**
     * Check if JewelryDesigner extension is enabled in the frontend area
     *
     * @return  bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Check if JewelryDesigner extension is enabled in the Admin Area
     *
     * @return  bool
     */
    public function isEnabledInAdmin()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED_IN_ADMIN);
    }

}
