<?php
/**
 * Extend/Override TinyBrick_OrderEdit module
 *
 * @category    Pan
 * @package     Pan_OrderEdit
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_OrderEdit_Helper_Data extends TinyBrick_OrderEdit_Helper_Data
{

    /**
     * Checks to see if the module is registered to the site
     *
     * =========================================================================
     * AAI OVERRIDE
     * -------------------------------------------------------------------------
     *
     * Enable module by hard coding our development and staging domains
     * =========================================================================
     *
     * @return boolean
     */
    public function _isRegistered()
    {
        Mage::log('hit');
        $baseUrl = Mage::getBaseUrl();
        if(preg_match('/127.0.0.1|localhost|192.168|pan.dev|staging.pandoramoa.com/', $baseUrl)){
            return true;
        }

        if($registeredDomain = Mage::getStoreConfig('toe/oej/nfg')){
            if(preg_match("/$registeredDomain/", $baseUrl)){
                if(($serial = Mage::getStoreConfig('toe/oej/wdf')) && $key = Mage::getStoreConfig('toe/oej/ntr')){
                    if(md5($registeredDomain.$serial) == $key){
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
