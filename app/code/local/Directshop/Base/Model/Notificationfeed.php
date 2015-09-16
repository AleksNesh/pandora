<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_Base
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

class Directshop_Base_Model_Notificationfeed extends Mage_AdminNotification_Model_Feed
{
	const DSBASE_XML_NOTIFICATION_ENABLED    = 'dsbase/notification/notification_enabled';
	const DSBASE_XML_USE_HTTPS_PATH    = 'dsbase/notification/use_https';
    const DSBASE_XML_FEED_URL_PATH     = 'dsbase/notification/feed_url';
    const DSBASE_XML_FREQUENCY_PATH    = 'dsbase/notification/frequency';
    const DSBASE_XML_LAST_UPDATE_PATH  = 'dsbase/notification/last_update';
    
	/**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::DSBASE_XML_FREQUENCY_PATH) * 3600;
    }
    
	/**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::DSBASE_XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . Mage::getStoreConfig(self::DSBASE_XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return Mage::app()->loadCache('directshop_dsbase_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'directshop_dsbase_notifications_lastcheck');
        return $this;
    }
}