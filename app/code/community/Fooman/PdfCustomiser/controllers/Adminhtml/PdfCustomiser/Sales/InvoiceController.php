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
class Fooman_PdfCustomiser_Adminhtml_PdfCustomiser_Sales_InvoiceController extends Fooman_PdfCustomiser_Adminhtml_PdfCustomiser_Sales_OrderController
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/invoice');
    }

    /**
     * print invoices from order_ids
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfinvoicesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $invoiceIds = $this->getRequest()->getPost('invoice_ids');
        $hideLogo = $this->getRequest()->getParam('hide_logo');
        //set background hiding via helper when included in request
        if ($this->getRequest()->getParam('hide_background')) {
            Mage::helper('pdfcustomiser/pdf_invoice')->setHideBackground($this->getRequest()->getParam('hide_background'));
        }
        if (sizeof($orderIds)) {
            Mage::getModel('pdfcustomiser/invoice')->renderPdf(null, $orderIds, null, false, '',
                            $this->getRequest()->getParam('force_store_id'), $hideLogo);
        } elseif (sizeof($invoiceIds)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $invoiceIds))
                ->load();
            Mage::getModel('pdfcustomiser/invoice')->renderPdf($invoices, null, null, false, '',
                            $this->getRequest()->getParam('force_store_id'), $hideLogo);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirectReferer('adminhtml/sales_order');
        }
        $this->_redirectReferer('adminhtml/sales_order');
    }

}