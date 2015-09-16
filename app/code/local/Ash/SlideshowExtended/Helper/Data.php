<?php

class Ash_SlideshowExtended_Helper_Data extends Ash_Slideshow_Helper_Data
{
    const ASH_SLIDE_EXT_ENABLED_PATH    = 'general/enabled';
    const ASH_SLIDE_EXT_SHOWON_PATH     = 'general/show';
    const ASH_SLIDE_EXT_SLIDER_PATH     = 'general/slider';

    /**
     * $_excludePages - Don't show slideshow on these pages
     * @var array
     */
    protected $_excludePages = array('login', 'forgotpassword', 'create');

    /**
     * isSlideshowEnabled()
     *
     * verify that the slideshow setting is enabled (true)
     * and that it can be displayed on specified pages
     *
     * @return bool
     */
    public function isSlideshowEnabled()
    {
        // Pull values from the store configuration (System > Configuration > Ash Slideshow (Extended))
        $enabled    = $this->getIsEnabled();
        $showOn     = $this->getShowOn();

        // request parameters broken out to individual vars
        $request    = $this->getRequest();
        $route      = $this->getRouteName();
        $module     = $this->getModuleName();
        $controller = $this->getControllerName();
        $action     = $this->getActionName();

        // default return value
        $show       = false;

        if ($enabled) {
            $show = true;
            if ($showOn == 'home') {
                $show = false;
                // make sure it really is the CMS home page
                if ($this->currentPageIsHomePage()) {
                    $show = true;
                }
            }
            // don't show slideshow at all for specific pages
            if ($show && ($route == 'customer' && in_array($action, $this->getExcludedPages()) )) {
                $show = false;
            }
        }
        return $show;
    }

    /**
     * getter method for $_excludePages
     *
     * @return  array
     */
    public function getExlcudedPages()
    {
        return $this->_excludePages;
    }

    /**
     * setter method for $_excludePages
     *
     * @var     array     $values
     * @return  array
     */
    public function setExcludedPages(array $values = array('login', 'forgotpassword', 'create'))
    {
        $this->_excludePages = $values;
        return $this;
    }

    /**
     * getIsEnabled
     *
     * get store configuration value for if the slideshow is enabled/disabled
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getConfigValue(self::ASH_SLIDE_EXT_ENABLED_PATH);
    }

    /**
     * getShowOn
     *
     * get store configuration value for what page(s) to show the slideshow
     *
     * @return string
     */
    public function getShowOn()
    {
        return $this->getConfigValue(self::ASH_SLIDE_EXT_SHOWON_PATH);
    }

    /**
     * getSlideshowId
     *
     * get store configuration value for what slideshow to use
     *
     * @return integer
     */
    public function getSlideshowId()
    {
        return $this->getConfigValue(self::ASH_SLIDE_EXT_SLIDER_PATH);
    }

    /**
     * getConfigValue
     *
     * get store configuration value for specified paths
     * within the Ash_SlideshowExtended configurations
     *
     * @var     string             $path
     * @var     string|integer     $storeId
     * @return  mixed
     */
    public function getConfigValue($path, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->getCurrentStoreId();
        }
        return Mage::getStoreConfig('ash_slideshowextended/' . $path, $storeId);
    }

    /**
     * getCurrentStoreId
     *
     * @return integer
     */
    public function getCurrentStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function currentPageIsHomePage()
    {
        $module     = $this->getModuleName();
        $controller = $this->getControllerName();
        $action     = $this->getActionName();
        $isHomePage = ($module === 'cms' && $controller === 'index' && $action === 'index') ? true : false;
        return $isHomePage;
    }

    public function getRequest()
    {
        return Mage::app()->getFrontController()->getRequest();
    }

    public function getRouteName()
    {
        return $this->getRequest()->getRouteName();
    }

    public function getModuleName()
    {
        return $this->getRequest()->getModuleName();
    }

    public function getControllerName()
    {
        return $this->getRequest()->getControllerName();
    }

    public function getActionName()
    {
        return $this->getRequest()->getActionName();
    }

}
