<?php
/**
 * Extend/Override Infomodus_Upslabel module
 *
 * @category    Pan_Infomodus
 * @package     Pan_Infomodus_Upslabel
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// explicitly require parent controller b/c controllers are not autoloaded
require_once(Mage::getModuleDir('controllers','Infomodus_Upslabel') .DS. 'Adminhtml' .DS. 'AutocreatelabelController.php');

# our Pan_Infomodusupslabel_Adminhtml_PdflabelsController
require_once 'PdflabelsController.php';


class Pan_Infomodusupslabel_Adminhtml_AutocreatelabelController extends Infomodus_Upslabel_Adminhtml_AutocreatelabelController
{
    /**
     * Gutted 90% of this function to move logic into the
     * Pan_Infomodusupslabel_Helper_Data::generateTrackingNumberAndLabelForOrder()
     * method so it can be called from other locations
     *
     * @see  Pan_Infomodusupslabel_Helper_Data
     *
     * @return mixed
     */
    public function indexAction()
    {
        try {
            $ptype      = $this->getRequest()->getParam('type');
            $type       = 'shipment';
            $order_ids  = $this->getRequest()->getParam($ptype . '_ids');

            /**
             * AAI HACK
             *
             * Generates a tracking number and UPS Label PDF
             * based off one or more orders
             */

            foreach ($order_ids AS $orderId) {
                Mage::helper('upslabel')->generateTrackingNumberAndLabelForOrder($orderId, $type, $ptype);
            }

            list($success, $pdf, $message) = Mage::helper('upslabel')->generatePdfForOrders($order_ids, $type, $ptype);


            if (!is_null($pdf)) {
                $pdfData = $pdf->render();
                header("Content-Disposition: inline; filename=result.pdf");
                header("Content-type: application/x-pdf");
                echo $pdfData;
                return true;
            }

            if (!$success && !empty($message)) {
                $this->_getSession()->addError($this->__($message));
                $this->_redirectReferer();
            }
            /**
             * END AAI HACK
             */
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return true;
    }


}
