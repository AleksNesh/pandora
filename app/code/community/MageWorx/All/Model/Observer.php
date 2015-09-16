<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_All
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx All extension
 *
 * @category   MageWorx
 * @package    MageWorx_All
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */
class MageWorx_All_Model_Observer
{
    /**
     * Remove not permitted groups from System Configuration Section
     *
     * @param  Varien_Event_Observer $observer
     * @return MageWorx_All_Model_Observer
     */
    public function restrictGroupsAcl($observer)
    {
        $editBlock = $observer->getEvent()->getBlock();

        if (!($editBlock instanceof Mage_Adminhtml_Block_System_Config_Edit)) {
            return $this;
        }

        $sectionCode = Mage::app()->getRequest()->getParam('section');
        if (false === strpos($sectionCode, 'mageworx')) {
            return $this;
        }

        $session = Mage::getSingleton('admin/session');
        $currentSection = Mage::getSingleton('adminhtml/config')->getSections()->$sectionCode;
        $groups = $currentSection->groups[0];
        foreach ($groups as $group => $object){
            if (!$session->isAllowed("system/config/$sectionCode/$group")){
                $currentSection->groups->$group = null;
            }
        }
        return $this;
    }
}