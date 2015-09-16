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
require_once(Mage::getModuleDir('controllers','Infomodus_Upslabel') .DS. 'Adminhtml' .DS. 'PdflabelsController.php');

class Pan_Infomodusupslabel_Adminhtml_PdflabelsController extends Infomodus_Upslabel_Adminhtml_PdflabelsController
{
    /**
     * =========================================================================
     * AAI HACK
     *
     * Add checking for collection size of UPS Labels so that we can redirect
     * back to the orders page and provide a helpful message instead of a
     * blank white page
     * =========================================================================
     */
    public function indexAction()
    {
        $ptype  = $this->getRequest()->getParam('type');
        $type   = 'shipment';
        $order_ids = $this->getRequest()->getParam($ptype . '_ids');
        if ($ptype == 'creditmemo') {
            $ptype  = 'shipment';
            $type   = 'refund';
        }

        // returns a boolean value and a string message
        // $resp === true   => creates the PDF of one/multiple labels
        // $resp === false  => returns false with helpful message to tell which orders don't have UPS Labels
        list($resp, $message) = $this->create($order_ids, $type, $ptype);

        if(!$resp && !empty($message)){
            $this->_getSession()->addError($this->__($message));
            $this->_redirectReferer();
        }
    }

    /**
     * =========================================================================
     * AAI HACK
     *
     * Add checking for collection size of UPS Labels so that we can redirect
     * back to the orders page and provide a helpful message instead of a
     * blank white page
     * =========================================================================
     */
    public static function create($order_ids, $type, $ptype)
    {
        list($success, $pdf, $message) = Mage::helper('upslabel')->generatePdfForOrders($order_ids, $type, $ptype);

        if (!$success && is_null($pdf)) {
            return array($success, $message);
        } else {
            if (!is_null($pdf)) {
                $pdfData = $pdf->render();

                header("Content-Disposition: inline; filename=result.pdf");
                header("Content-type: application/x-pdf");
                echo $pdfData;
                return array(true, $message);
            }
        }
    }
}
