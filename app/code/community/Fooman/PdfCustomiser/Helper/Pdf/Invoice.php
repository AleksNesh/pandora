<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Helper_Pdf_Invoice extends Fooman_PdfCustomiser_Helper_Pdf
{
    /**
     * return column order and width for invoice
     * either use default or json_decode value from Advanced field in the back-end
     *
     * @param void
     *
     * @return array
     * @access public
     */
    public function getColumnOrderAndWidth()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicecolumnwidths'])) {
            if (Mage::getStoreConfig('sales_pdf/invoice/invoicecolumnwidths', $this->getStoreId())) {
                $this->_parameters[$this->getStoreId()]['invoicecolumnwidths'] =
                    json_decode(
                        Mage::getStoreConfig('sales_pdf/invoice/invoicecolumnwidths', $this->getStoreId()),
                        true
                    );
            } else {
                $this->_parameters[$this->getStoreId()]['invoicecolumnwidths'] =
                    $this->getDefaultColumnOrderAndWidth();
            }
        }
        return $this->_parameters[$this->getStoreId()]['invoicecolumnwidths'];
    }

    /**
     * get main heading for invoice title ie TAX INVOICE
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfTitle()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicetitle'])) {
            $this->_parameters[$this->getStoreId()]['invoicetitle'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoicetitle', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicetitle'];
    }

    /**
     * get tax number
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfInvoiceTaxNumber()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicetaxnumber'])) {
            $this->_parameters[$this->getStoreId()]['invoicetaxnumber'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoicetaxnumber', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicetaxnumber'];
    }

    /**
     * should we fake the invoice date as delivery date?
     *
     * @param void
     *
     * @return bool
     * @access public
     */
    public function getPdfInvoiceDeliveryDate()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicedeliverydate'])) {
            $this->_parameters[$this->getStoreId()]['invoicedeliverydate'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoicedeliverydate', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicedeliverydate'];
    }

    /**
     * return which addresses to display
     *
     * @param void
     *
     * @return  string billing/shipping/both
     * @access public
     */
    public function getPdfAddresses()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoiceaddresses'])) {
            $this->_parameters[$this->getStoreId()]['invoiceaddresses'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoiceaddresses', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoiceaddresses'];
    }

    /**
     * custom text for underneath invoice
     *
     * @param void
     *
     * @return  string
     * @access public
     */
    public function getPdfCustom()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicecustom'])) {
            $this->_parameters[$this->getStoreId()]['invoicecustom'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoicecustom', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicecustom'];
    }

    /**
     * are we using integrated labels - what to print?
     *
     * @param void
     *
     * @return  mixed bool / string
     * @access public
     */
    public function getPdfIntegratedLabels()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoiceintegratedlabels'])) {
            $this->_parameters[$this->getStoreId()]['invoiceintegratedlabels'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoiceintegratedlabels', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoiceintegratedlabels'];
    }

    /**
     * are we displaying the order increment id on the invoice?
     *
     * @param void
     *
     * @return bool
     * @access public
     */
    public function getPutOrderId()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoiceputorderid'])) {
            $this->_parameters[$this->getStoreId()]['invoiceputorderid'] = Mage::getStoreConfig(
                Mage_Sales_Model_Order_Pdf_Abstract::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                $this->getStoreId()
            );
        }
        return $this->_parameters[$this->getStoreId()]['invoiceputorderid'];
    }

    /**
     * returns the text preceding the invoice increment id
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getNumberText()
    {
        return Mage::helper('sales')->__('Invoice #');
    }

    /**
     * returns additional text to be displayed
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getTopAdditional()
    {
        $extras = '';
        if ($this->getPdfInvoiceTaxNumber()) {
            $extras .= '<br/>' . $this->getPdfInvoiceTaxNumber();
        }
        if ($this->getPdfInvoiceDeliveryDate()) {
            $extras .= '<br/>' . Mage::helper('pdfcustomiser')->__('Delivery Date') . ': '
                .Mage::helper('core')->formatDate($this->getSalesObject()->getCreatedAtStoreDate(), 'medium', false);
        }
        return $extras;
    }

    /**
     * return which columns the user chosen to display on the invoice
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfColumns()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicecolumns'])) {
            $this->_parameters[$this->getStoreId()]['invoicecolumns'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoicecolumns', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicecolumns'];
    }

    /**
     * return what column values should be used to reorder items
     *
     * @return bool
     * @access public
     */
    public function getColumnsSortOrder()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicecolumnssort'])) {
            $this->_parameters[$this->getStoreId()]['invoicecolumnssort'] =
                Mage::getStoreConfig('sales_pdf/invoice/invoicecolumnssort', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicecolumnssort'];
    }

    /**
     * should we print a barcode of the increment id
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function getPrintSalesObjectBarcode()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['invoicebarcode'])) {
            $this->_parameters[$this->getStoreId()]['invoicebarcode'] =
                Mage::getStoreConfig('sales_pdf/invoice/printbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['invoicebarcode'];
    }

    /**
     * get hideBackground
     * @return mixed
     * @access public
     */
    public function getHideBackground()
    {
        return $this->_hideBackground;
    }

    /**
     * set hideBackground
     * @param mixed
     * @return void
     * @access public
     */
    public function setHideBackground($request)
    {
        $this->_hideBackground = $request;
    }
}
