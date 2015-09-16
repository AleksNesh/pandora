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
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_DlController extends Mage_Core_Controller_Front_Action
{    
    
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

    public function fileAction()
    {
        // orderspro/dl/file/id/1/file.png
        $fileId = (int) $this->getRequest()->getParam('id');
        $files = Mage::getSingleton('mageworx_orderspro/upload_files')->load($fileId);
        $helper = Mage::helper('mageworx_orderspro');

        if ($files->getId()) {            
            $file = $helper->isUploadFile($fileId);
            if (empty($file)) {
                Mage::throwException($helper->__('Sorry, there was an error getting the file'));
                return $this->_redirectReferer();            
            } 
            try {
                $helper->processDownload($file, $files->getFileName());
                exit;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                return $this->_redirect('/');
            }
        } else {                        
            $this->_getSession()->addNotice($helper->__('Requested file not available now'));
            return $this->_redirectReferer();
        }
        return $this->_redirectReferer();
    }        
    
}
