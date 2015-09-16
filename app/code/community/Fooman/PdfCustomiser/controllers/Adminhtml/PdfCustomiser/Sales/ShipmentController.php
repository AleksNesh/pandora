<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once BP.'/app/code/community/Fooman/PdfCustomiser/controllers/Adminhtml/PdfCustomiser/Sales/OrderController.php';
class Fooman_PdfCustomiser_Adminhtml_PdfCustomiser_Sales_ShipmentController extends Fooman_PdfCustomiser_Adminhtml_PdfCustomiser_Sales_OrderController
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');
    }

    /**
     * override EmailAttachment behaviour to print based on order_ids
     * to allow printing without having first created shipments
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfshipmentsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $shipmentIds = $this->getRequest()->getPost('shipment_ids');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_shipment')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        $print = false;
        if (sizeof($orderIds)) {
            if (!Fooman_PdfCustomiser_Model_Abstract::COMPAT_MODE) {
                Mage::getModel('pdfcustomiser/shipment')->renderPdf(null, $orderIds, null, false, '',
                        $this->getRequest()->getParam('force_store_id'), $hideLogo);
                $this->_redirectReferer('adminhtml/sales_order');
            } else {
                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(null, $orderIds);
                $print = $pdf->render();
                if ($print) {
                    return $this->_prepareDownloadResponse(
                        'shipments_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                        $print,
                        'application/pdf'
                    );
                }
            }
        } elseif (sizeof($shipmentIds)) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))
                ->load();
            Mage::getModel('pdfcustomiser/shipment')->renderPdf($shipments, null, null, false, '',
                $this->getRequest()->getParam('force_store_id'), $hideLogo);
        } else {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }


}