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
 * Pan_JewelryDesigner Isadmin helper
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @author      August Ash Team <core@augustash.com>
 */
class Pan_JewelryDesigner_Helper_Isadmin extends Mage_Core_Helper_Abstract
{
    public function isAdminArea()
    {
        if (Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml') {
            return true;
        } else {
            return false;
        }
    }

    public function isAdminUser()
    {
        return $isAdmin = ($this->getAdminUser()) ? true : false;
    }

    public function getAdminUser()
    {
        return $adminUser = ($this->_getAdminUserId()) ? Mage::getSingleton('admin/session')->getUser() : null;
    }

    protected function _getAdminUserId()
    {
        return Mage::helper('adminhtml')->getCurrentUserId();
    }

}
