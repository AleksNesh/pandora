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
 * Extensions Controller
 *
 * @category    Ash_Up
 * @package     Ash_Up_Adminhtml
 */
class Ash_Up_Adminhtml_AshinstallerController extends Mage_Adminhtml_Controller_action
{
    /**
     * Initialize action -- set the breadcrumbs and the active menu
     *
     * @return  Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ash/ash_up')
            ->_title($this->__('Ash Installer'))
            ->_addBreadcrumb($this->__('Ash Installer'), $this->__('Ash Installer'));

        return $this;
    }

    /**
     * Index action
     *
     * @return  void
     */
    public function indexAction()
    {
        // PHP extension checks
        if (!extension_loaded('zip')) {
            Mage::getSingleton('adminhtml/session')
                ->addError('Zip PHP extension is not installed! Cannot install extensions.');
        }
        if (Mage::getStoreConfig('ash_up/ftp/enabled') && !extension_loaded('ftp')) {
            Mage::getSingleton('adminhtml/session')
                ->addError('FTP PHP extension is not installed! Cannot install extensions using FTP');
        }

        $this->_initAction()
             ->renderLayout();
    }

    /**
     * CheckUpdates action
     *
     * @return  void
     */
    public function checkupdatesAction()
    {
        try {
            Mage::helper('ash_up')->checkForUpdates();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ash_up')->__('Version updates were requested'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    /**
     * Install new or upgrade existing action
     *
     * @return  void
     */
    public function massUpgradeAction()
    {
        try {
            $extensions = $this->getRequest()->getPost('extensions');
            if (!$extensions) {
                Mage::throwException($this->__('No extensions to install/upgrade'));
            }

            Mage::helper('ash_up')->upgradeExtensions($extensions);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ash_up')->__('Extensions have been installed/upgraded'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return  boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ash/ash_up');
    }

    /**
     * Render grid
     *
     * @return  void
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ash_up/adminhtml_extension_grid', 'extension.grid')
                ->toHtml()
        );
    }
}
