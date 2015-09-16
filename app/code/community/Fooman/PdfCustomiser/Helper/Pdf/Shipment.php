<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Helper_Pdf_Shipment extends Fooman_PdfCustomiser_Helper_Pdf
{

    /**
     * return column order and width for shipment
     * either use default or json_decode value from Advanced field in the back-end
     *
     * @param void
     *
     * @return array
     * @access public
     */
    public function getColumnOrderAndWidth()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentcolumnwidths'])) {
            if (Mage::getStoreConfig('sales_pdf/shipment/shipmentcolumnwidths', $this->getStoreId())) {
                $this->_parameters[$this->getStoreId()]['shipmentcolumnwidths'] =
                    json_decode(
                        Mage::getStoreConfig('sales_pdf/shipment/shipmentcolumnwidths', $this->getStoreId()),
                        true
                    );
            } else {
                $this->_parameters[$this->getStoreId()]['shipmentcolumnwidths'] =
                    $this->getDefaultColumnOrderAndWidth();
            }
        }
        return $this->_parameters[$this->getStoreId()]['shipmentcolumnwidths'];
    }

    /**
     * get main heading for invoice title ie PACKING SLIP
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getPdfTitle()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['shipmenttitle'])) {
            $this->_parameters[$this->getStoreId()]['shipmenttitle'] =
                Mage::getStoreConfig('sales_pdf/shipment/shipmenttitle', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmenttitle'];
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
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentaddresses'])) {
            $this->_parameters[$this->getStoreId()]['shipmentaddresses'] =
                Mage::getStoreConfig('sales_pdf/shipment/shipmentaddresses', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmentaddresses'];
    }

    /**
     * custom text for underneath order
     *
     * @param void
     *
     * @return  string
     * @access public
     */
    public function getPdfCustom()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentcustom'])) {
            $this->_parameters[$this->getStoreId()]['shipmentcustom'] =
                Mage::getStoreConfig('sales_pdf/shipment/shipmentcustom', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmentcustom'];
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
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentintegratedlabels'])) {
            $this->_parameters[$this->getStoreId()]['shipmentintegratedlabels'] =
                Mage::getStoreConfig('sales_pdf/shipment/shipmentintegratedlabels', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmentintegratedlabels'];
    }

    /**
     * are we displaying the order increment id on the shipment?
     *
     * @param void
     *
     * @return bool
     * @access public
     */
    public function getPutOrderId()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentputorderid'])) {
            $this->_parameters[$this->getStoreId()]['shipmentputorderid'] = Mage::getStoreConfig(
                Mage_Sales_Model_Order_Pdf_Abstract::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                $this->getStoreId()
            );
        }
        return $this->_parameters[$this->getStoreId()]['shipmentputorderid'];
    }

    /**
     * returns the text preceding the shipment increment id
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getNumberText()
    {
        return trim(Mage::helper('sales')->__('Packingslip # '));
    }

    /**
     * should we display totals? turn off for packing slips
     *
     * @return bool
     * @access public
     */
    public function displayTaxSummary()
    {
        return false;
    }

    /**
     * should we display the gift message? turn on for packing slip
     *
     * @param void
     *
     * @return bool
     * @access public
     */
    public function displayGiftMessage()
    {
        return true;
    }

    /**
     * should we display totals on this pdf?
     *
     * @param void
     * @access public
     *
     * @return bool
     */
    public function displayTotals()
    {
        return false;
    }

    /**
     * which columns should be displayed on the shipment?
     *
     * @param void
     * @access public
     *
     * @return mixed
     */
    public function getPdfColumns()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentcolumns'])) {
            $this->_parameters[$this->getStoreId()]['shipmentcolumns'] =
                Mage::getStoreConfig('sales_pdf/shipment/shipmentcolumns', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmentcolumns'];
    }

    /**
     * return what column values should be used to reorder items
     *
     * @return bool
     * @access public
     */
    public function getColumnsSortOrder()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentcolumnssort'])) {
            $this->_parameters[$this->getStoreId()]['shipmentcolumnssort'] =
                Mage::getStoreConfig('sales_pdf/shipment/shipmentcolumnssort', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmentcolumnssort'];
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
        if (!isset($this->_parameters[$this->getStoreId()]['shipmentbarcode'])) {
            $this->_parameters[$this->getStoreId()]['shipmentbarcode'] =
                Mage::getStoreConfig('sales_pdf/shipment/printbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['shipmentbarcode'];
    }

    /**
     * get hideBackground
     * @return  mixed
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
