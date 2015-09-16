<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Helper_Data extends Fooman_EmailAttachments_Helper_Data
{
    const LOG_FILE_NAME='fooman_pdfcustomiser.log';

    /**
     * convert pdf object to string and attach to mail object
     *
     * @param        $pdf
     * @param        $mailObj
     * @param string $name
     *
     * @return mixed
     */
    public function addAttachment($pdf, $mailObj, $name = "order.pdf")
    {
        try {
            $this->debug('ADDING ATTACHMENT: '.$name);
            if ($this->writePdfsToDisk()) {
                $dir = Mage::getBaseDir().DS.'media'.DS.'pdfs';
                if (file_exists($dir)) {
                    $pdfFileName = $dir . DS . $name . '.pdf';
                    if (file_exists($pdfFileName)) {
                        //uncomment here to delete existing files and keep the last sent pdf
                        //unlink($pdfFileName);
                    }
                    if (!file_exists($pdfFileName)) {
                        $pdf->render(null, null, $pdfFileName);
                    }
                }
            }
            $file = $pdf->render();
            if (!($mailObj instanceof Zend_Mail)) {
                $mailObj = $mailObj->getMail();
            }
            $mailObj->createAttachment(
                $file,
                'application/pdf',
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $name . '.pdf'
            );
            $this->debug('FINISHED ADDING ATTACHMENT: '.$name);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $mailObj;
    }

    /**
     * log debug message if in debug mode
     *
     * @param $msg
     */
    public function debug($msg)
    {
        if ($this->isDebugMode()) {
            Mage::log($msg, null, self::LOG_FILE_NAME);
        }
    }

    /**
     * are we in debug mode?
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return false;
    }

    /**
     * should we write pdf email attachments to disk?
     *
     * @return bool
     */
    public function writePdfsToDisk()
    {
        return false;
    }

    public function getCalculatedTaxes($salesObject, $includeShipping = false)
    {
        $filteredTaxrates = array();
        if (!$salesObject instanceof Mage_Sales_Model_Order) {
            $order = $salesObject->getOrder();
            if ($order->getTaxAmount() != $salesObject->getTaxAmount()) {
                //Magento looses information of tax rates if an order
                //is split into multiple invoices
                //try getCalculateTaxes approach
                $taxHelper = Mage::helper('tax');
                if (method_exists($taxHelper, 'getCalculatedTaxes')) {
                    $taxes = Mage::helper('pdfcustomiser/tax')->getCalculatedTaxFixed($salesObject);
                    $returnArray = array();
                    $runningTotal = 0;
                    if ($taxes) {
                        foreach ($taxes as $tax) {
                            $returnArray[] = array(
                                'id'         => $tax['title'],
                                'percent'    => $tax['percent'],
                                'amount'     => $tax['tax_amount'],
                                'baseAmount' => $tax['base_tax_amount'],
                                'title'      => $tax['title']
                            );
                            $runningTotal += $tax['base_tax_amount'];
                        }
                    }
                    // the running total check is intended to cover cases where
                    // sales_order_tax_item has not been populated
                    if (!empty($returnArray) && $runningTotal >= $salesObject->getBaseTaxAmount()) {
                        return $returnArray;
                    }
                }
                //couldn't retrieve a correct tax breakdown - only display summary
                return false;
            }
        } else {
            $order = $salesObject;
        }
        //need to filter out doubled up taxrates on edited/reordered items -> Magento bug
        foreach ($order->getFullTaxInfo() as $taxrate) {
            //The shipping and handling tax entry is already included in taxes
            //if display is required it can be achieved through enabling it
            //under Pdf Totals Sort Order
            if (!isset($taxrate['rates'])) {
                continue;
            }
            if (isset($taxrate['title']) && $taxrate['title'] == Mage::helper('sales')->__('Shipping & Handling Tax')) {
                continue;
            }
            foreach ($taxrate['rates'] as $rate) {
                $taxId = str_replace(array('%', ' '), '', $rate['code']);
                if (!isset($rate['title'])) {
                    $rate['title'] = $taxId;
                }

                if (isset($taxrate['amount'])) {
                    $filteredTaxrates[$taxId] = array(
                        'id'         => $taxId,
                        'percent'    => $rate['percent'],
                        'amount'     => $taxrate['amount'],
                        'baseAmount' => $taxrate['base_amount'],
                        'title'      => $rate['title']
                    );
                    unset($taxrate['amount']);
                } else {
                    $filteredTaxrates[$taxId] = array(
                        'id'         => $taxId,
                        'percent'    => $rate['percent'],
                        'amount'     => null,
                        'baseAmount' => null,
                        'title'      => $rate['title']
                    );
                }
            }
        }
        return $filteredTaxrates;
    }

    public function addButton($block)
    {
        parent::addButton($block);
        if (Mage::getStoreConfigFlag('sales_pdf/shipment/shipmentuseorder')) {
            $block->addButton(
                'fooman_print_packingslip', array(
                    'label'   => Mage::helper('sales')->__('Print Packing Slip'),
                    'class'   => 'save',
                    'onclick' => 'setLocation(\'' . $this->getPrintPackingSlipUrl($block) . '\')'
                )
            );
            if (Mage::getStoreConfig('sales_pdf/all/allprintaltstore') != 0) {
                $block->addButton(
                    'print_altstore', array(
                        'label' => Mage::helper('sales')->__(
                                'Print from ' . Mage::app()->getStore(
                                    Mage::getStoreConfig('sales_pdf/all/allprintaltstore')
                                )->getName()
                            ),
                        'class' => 'save',
                        'onclick' =>
                            'setLocation(\'' . $this->getPrintUrl($block) . 'force_store_id/' . Mage::getStoreConfig(
                                'sales_pdf/all/allprintaltstore'
                            ) . '/' . '\')'
                    )
                );
                $block->addButton(
                    'fooman_print_altstore', array(
                        'label'   => Mage::helper('sales')->__(
                                'Print Packing Slip from ' . Mage::app()->getStore(
                                    Mage::getStoreConfig('sales_pdf/all/allprintaltstore')
                                )->getName()
                            ),
                        'class'   => 'save',
                        'onclick' => 'setLocation(\'' . $this->getPrintPackingSlipUrl($block) . 'force_store_id/'
                            . Mage::getStoreConfig('sales_pdf/all/allprintaltstore') . '/' . '\')'
                    )
                );
            }
        }
    }

    /**
     * return url to print single order from order > view
     *
     * @param void
     * @access protected
     *
     * @return string
     */
    protected function getPrintUrl($block)
    {
        $params = array('order_id' => $block->getOrder()->getId());
        $hideLogoBgParams = Mage::helper('pdfcustomiser/pdf_order')->getPdfLogoBgUrlParams();
        $combinedParams = array_merge($params, $hideLogoBgParams);
        if (Mage::helper('core')->isModuleEnabled('Fooman_PdfCustomiser')) {
            return $block->getUrl(
                'adminhtml/pdfCustomiser_sales_order/print',
                $combinedParams
            );
        } else {
            return $block->getUrl(
                'emailattachments/admin_order/print',
                array('order_id' => $block->getOrder()->getId())
            );
        }
    }

    /**
     * return url to print packing slip from order > view
     *
     * @param $block
     *
     * @access protected
     *
     * @return string
     */
    protected function getPrintPackingSlipUrl($block)
    {

        return $block->getUrl(
            'adminhtml/pdfCustomiser_sales_order/pdfshipment',
            array('order_id' => $block->getOrder()->getId())
        );
    }

    public function getOrderAttachmentName($order)
    {
        $helper = Mage::helper('pdfcustomiser/pdf_order');
        $helper->setStoreId($order->getStoreId());
        return $helper->getPdfFileName(array($order->getIncrementId()), '');
    }

    public function getInvoiceAttachmentName($invoice)
    {
        $helper = Mage::helper('pdfcustomiser/pdf_invoice');
        $helper->setStoreId($invoice->getStoreId());
        return $helper->getPdfFileName(array($invoice->getIncrementId()), '');
    }

    public function getShipmentAttachmentName($shipment)
    {
        $helper = Mage::helper('pdfcustomiser/pdf_shipment');
        $helper->setStoreId($shipment->getStoreId());
        return $helper->getPdfFileName(array($shipment->getIncrementId()), '');
    }

    public function getCreditmemoAttachmentName($creditmemo)
    {
        $helper = Mage::helper('pdfcustomiser/pdf_creditmemo');
        $helper->setStoreId($creditmemo->getStoreId());
        return $helper->getPdfFileName(array($creditmemo->getIncrementId()), '');
    }
}
