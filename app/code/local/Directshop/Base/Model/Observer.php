<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_Base
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */
 
class Directshop_Base_Model_Observer
{
	
	public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn() && Mage::getStoreConfigFlag(Directshop_Base_Model_Notificationfeed::DSBASE_XML_NOTIFICATION_ENABLED)) 
        {
            $feedModel  = Mage::getModel('dsbase/notificationfeed');
            $feedModel->checkUpdate();
        }
    }
    
}