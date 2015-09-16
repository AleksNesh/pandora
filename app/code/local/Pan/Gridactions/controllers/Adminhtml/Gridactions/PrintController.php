<?php

/**
 * Extend/Override Xtento_GridActions module
 *
 * @category    Pan
 * @package     Pan_Gridactions
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


// explicitly require parent controller b/c controllers are not autoloaded
require_once(Mage::getModuleDir('controllers','Xtento_GridActions') .DS. 'Adminhtml' .DS. 'Gridactions' .DS. 'PrintController.php');

class Pan_Gridactions_Adminhtml_Gridactions_PrintController extends Xtento_GridActions_Adminhtml_GridActions_PrintController
{

    /**
     * Print shipping labels for selected orders, only for supported carriers,
     * just like the "Print shipping labels" mass action
     */
    public function pdflabelsAction()
    {
        $orderIds = explode(",", $this->getRequest()->getParam('order_ids'));
        if (!empty($orderIds)) {
            //foreach ($orderIds as $orderId) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderIds) // Be careful: Could be because of PdfCustomizer extension. Should be $orderId - why does the PDF get returned instantly?
                ->load();


            if ($shipments && $shipments->getSize()) {
                foreach ($shipments as $shipment) {
                    $labelContent = $shipment->getShippingLabel();
                    if ($labelContent) {
                        $labelsContent[] = $labelContent;
                    }
                    /**
                     * BEGIN AAI HACK
                     */
                    else {
                        $order          = $shipment->getOrder();

                        $upsMethod      = Mage::helper('upslabel')->checkIfUpsShippingMethod($order);
                        $postalMethod   = Mage::helper('upslabel')->checkIfPostalShippingMethod($order);

                        if (!empty($upsMethod)) {
                            Mage::helper('upslabel')->generateTrackingNumberAndLabelForOrder($order->getId());

                            // we have a UPS shipping option selected so create a shipping label
                            $pdfLabel = Mage::helper('upslabel')->generateUpsLabelPdfForOrder($order->getId());

                            $shipment->setData('shipping_label', $pdfLabel->render());
                            $shipment->save();

                            $labelsContent[] = $pdfLabel->render();
                        } else {
                            if (!empty($postalMethod)) {
                                $this->_getSession()->addError(Mage::helper('sales')->__("Cannot auto-create label for USPS '" . $postalMethod . "' because it is an unsupported carrier at this time. Can only generate shipping labels for UPS shipping methods."));
                            } else {
                                $this->_getSession()->addError(Mage::helper('sales')->__("Cannot auto-create label for an unsupported carrier at this time. Can only generate shipping labels for UPS shipping methods."));
                            }
                        }
                        /**
                         * END AAI HACK
                         */

                        // $shipment = $shipment->load($shipment->getId());

                    }
                }
            }
            if (!empty($labelsContent)) {
                $outputPdf = $this->_combineLabelsPdf($labelsContent);
                $this->_prepareDownloadResponse('ShippingLabels.pdf', $outputPdf->render(), 'application/pdf');
                return;
            } else {
                $this->_getSession()->addError(Mage::helper('sales')->__('There are no shipping labels related to selected order.'));
            }
        }
        $this->_redirect('adminhtml/sales_order');
    }

}
