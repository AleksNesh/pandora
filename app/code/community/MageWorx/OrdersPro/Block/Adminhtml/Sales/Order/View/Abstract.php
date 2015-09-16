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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
if ((string)Mage::getConfig()->getModuleConfig('MageTools_Pendingorders')->active == 'true') {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends MageTools_Pendingorders_Block_Sales_Order_View {}
/*} else if ((string)Mage::getConfig()->getModuleConfig('Fooman_PdfCustomiser')->active == 'true') {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends Fooman_PdfCustomiser_Block_View {}
} else if ((string)Mage::getConfig()->getModuleConfig('Fooman_EmailAttachments')->active == 'true') {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends Fooman_EmailAttachments_Block_View {} */
} else if ((string)Mage::getConfig()->getModuleConfig('IllApps_Shipsync')->active=='true') {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends  IllApps_Shipsync_Block_Adminhtml_Sales_Order_View {}
} else if ((string)Mage::getConfig()->getModuleConfig('AuIt_Pdf')->active == 'true') {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends AuIt_Pdf_Block_Adminhtml_Sales_Order_View {}
} else if ((string)Mage::getConfig()->getModuleConfig('Amasty_Email')->active=='true') {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends  Amasty_Email_Block_Adminhtml_Sales_Order_View {}
}  else {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_View_Abstract extends Mage_Adminhtml_Block_Sales_Order_View {}
}