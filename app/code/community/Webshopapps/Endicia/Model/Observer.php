<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Endicia
 * User         Genevieve Eddison
 * Date         13 November 2013
 * Time         11:00 AM
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */
class Webshopapps_Endicia_Model_Observer extends Mage_Core_Model_Abstract
{

    /*
     * Set shipping method on shipment
     *
     */
    public function adminhtmlControllerActionPredispatchStart($observer)
    {
        if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/labels')) {
            return;
        }
        if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhZW5kaWNpYS9zaGlwX29uY2U=',
            'bGVnb2xpZ2h0','Y2FycmllcnMvd3NhZW5kaWNpYS9zZXJpYWw=')) {
           Mage::helper('wsalogger/log')->postCritical('Webshopapps Endicia', base64_decode('TGljZW5zZQ=='), base64_decode('U2VyaWFsIEtleSBJbnZhbGlk'));
            return null;
        }

        $request = Mage::app()->getFrontController()->getRequest();
        if(strstr($request->getControllerName(), 'sales_order_shipment') && strstr($request->getActionName(), 'createLabel')) {

            $request = Mage::app()->getRequest();
            $request->initForward()
                ->setControllerName('shipment')
                ->setModuleName('wsaendicia')
                ->setActionName('createEndiciaLabel')
                ->setDispatched(false);

        }
        elseif (strstr($request->getControllerName(), 'sales_order_shipment') && strstr($request->getActionName(), 'save')) {
            $request = Mage::app()->getRequest();
            $request->initForward()
                ->setControllerName('shipment')
                ->setModuleName('wsaendicia')
                ->setActionName('save')
                ->setDispatched(false);
        }
    }

    public function coreBlockAbstractToHtmlBefore($observer)
     {
         if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/labels')) {
             return;
         }

         if( $observer->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form) {
             if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhZW5kaWNpYS9zaGlwX29uY2U=',
                 'bGVnb2xpZ2h0','Y2FycmllcnMvd3NhZW5kaWNpYS9zZXJpYWw=')) {
                 Mage::helper('wsalogger/log')->postCritical('Webshopapps Endicia', base64_decode('TGljZW5zZQ=='), base64_decode('U2VyaWFsIEtleSBJbnZhbGlk'));
            	return null;
             }
             $observer->getBlock()->setTemplate('webshopapps_endicia/sales/order/shipment/view/form.phtml');

         }

         elseif($observer->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Items) {
             if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhZW5kaWNpYS9zaGlwX29uY2U=',
                 'bGVnb2xpZ2h0','Y2FycmllcnMvd3NhZW5kaWNpYS9zZXJpYWw=')) {
                 Mage::helper('wsalogger/log')->postCritical('Webshopapps Endicia', base64_decode('TGljZW5zZQ=='), base64_decode('U2VyaWFsIEtleSBJbnZhbGlk'));
            	return null;
             }
             $observer->getBlock()->setTemplate('webshopapps_endicia/sales/order/shipment/create/items.phtml');
         }
    }
    
    public function postError($observer) {
    	if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhZW5kaWNpYS9zaGlwX29uY2U=',
                 'bGVnb2xpZ2h0','Y2FycmllcnMvd3NhZW5kaWNpYS9zZXJpYWw=')) {
				$session = Mage::getSingleton('adminhtml/session');
				$session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIGtleSBpcyBub3QgdmFsaWQgZm9yIFdlYnNob3BhcHBzIEVuZGljaWE=')));
     	}
	}
    

}