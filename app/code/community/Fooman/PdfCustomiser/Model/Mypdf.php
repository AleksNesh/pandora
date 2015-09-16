<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// load alternative config file
require_once(BP . DS . 'lib' . DS . 'tcpdf' . DS . 'config'. DS . 'tcpdf_config_mage.php');
require_once(BP . DS . 'lib' . DS . 'tcpdf' . DS . 'tcpdf.php');

class Fooman_PdfCustomiser_Model_Mypdf extends TCPDF
{

    const FACTOR_PIXEL_PER_MM = 3;

    public $shippingTaxRate = '';
    public $surchargeTaxRate = '';
    public $giftwrappingTaxRate = '';
    public $cashOnDeliveryTaxRate = '';
    public $canPrintTaxSummary = true;

    protected $_taxTotal = array();
    protected $_taxAmount = array();
    protected $_hiddenTaxAmount = 0;
    protected $_baseHiddenTaxAmount = 0;
    protected $_pdfItems = array();
    protected $_pdfBundleItems = array();

    private $_sortBy;

    /**
     * override standard constructor so we can use Magento's factory
     * and pass in additional constructor arguments
     *
     * @param array $arguments array of 7 constructor arguments
     */
    public function __construct($arguments)
    {
        $cacheDir = Mage::getConfig()->getOptions()->getDir('cache');
        $pdfDir = $cacheDir . DS . 'pdfcache';
        Mage::getConfig()->getOptions()->createDirIfNotExists($pdfDir);
        list($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa) = $arguments;
        return parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    /**
     * storeId
     * @access protected
     */
    protected $_storeId;

    /**
     * get storeId
     *
     * @return  int
     * @access public
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * set store id for later processing of store relevant settings
     *
     * @param int $storeId the store's id
     *
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * helper
     * @access protected
     */
    protected $_pdfHelper;

    /**
     * get helper
     *
     * @return Fooman_PdfCustomiser_Helper_Pdf
     */
    public function getPdfHelper()
    {
        return $this->_pdfHelper;
    }

    /**
     * set helper
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     *
     * @return  void
     */
    public function setPdfHelper(Fooman_PdfCustomiser_Helper_Pdf $helper)
    {
        $this->_pdfHelper = $helper;
    }

    protected $_incrementId;

    /**
     * @return mixed
     */
    public function getIncrementId()
    {
        return $this->_incrementId;
    }

    /**
     * @param $id
     */
    public function setIncrementId($id)
    {
        $this->_incrementId = $id;
    }

    /**
     * keep track if we have output
     * @access protected
     */
    protected $_pdfAnyOutput = false;

    /**
     * do we have output?
     * @return  bool
     * @access public
     */
    public function getPdfAnyOutput()
    {
        return $this->_pdfAnyOutput;
    }

    /**
     * set _pdfAnyOutput
     *
     * @param $flag
     *
     * @return  void
     * @access public
     */
    public function setPdfAnyOutput($flag)
    {
        $this->_pdfAnyOutput = $flag;
    }

    /**
     * retrieve line items
     *
     * @param \Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                  $printItem
     * @param null                             $order
     *
     * @internal param $
     * @return void
     * @access   public
     */
    public function prepareLineItems(Fooman_PdfCustomiser_Helper_Pdf $helper, $printItem, $order = null)
    {
        //reset
        $this->_taxTotal = array();
        $this->_taxAmount = array();
        $this->_hiddenTaxAmount = 0;
        $this->_baseHiddenTaxAmount = 0;
        $this->_pdfItems = array();
        $this->_pdfBundleItems = array();

        //prepare settings
        if (Mage::getStoreConfig('tax/sales_display/price', $helper->getStoreId()) ==
            Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
            || Mage::getStoreConfig('tax/sales_display/price', $helper->getStoreId()) ==
                Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
        ) {
            $displayItemTaxInclusive = true;
        } else {
            $displayItemTaxInclusive = false;
        }
        if (Mage::getStoreConfigFlag('sales_pdf/all/allrowtotaltaxinclusive', $helper->getStoreId())) {
            $displaySubtotalTaxInclusive = true;
        } else {
            $displaySubtotalTaxInclusive = false;
        }
        $displayTaxInclusiveHiddenTaxAmount = false;
        if (Mage::getStoreConfig('tax/calculation/apply_after_discount', $helper->getStoreId())) {
            $displayTaxAfterDiscount = true;
        } else {
            $displayTaxAfterDiscount = false;
        }
        if (Mage::getStoreConfig('tax/weee/display_sales', $helper->getStoreId()) <= 2) {
            $displayFptInclusivePrices = true;
        } else {
            $displayFptInclusivePrices = false;
        }

        //loop over all items of the sales object
        foreach ($printItem->getAllItems() as $item) {
            $pdfTemp = array();

            //check if we are printing an order
            if ($item instanceof Mage_Sales_Model_Order_Item) {
                $isOrderItem = true;
                $orderItem = $item;
                $pdfTemp['qty'] = $helper->getPdfQtyAsInt() ? (int)$item->getQtyOrdered() : $item->getQtyOrdered();
                $pdfTemp['qty_ordered'] = $pdfTemp['qty'];
                $pdfTemp['qty_backordered'] = $helper->getPdfQtyAsInt() ? (int)$item->getQtyBackordered() : $item->getQtyBackordered();
                $pdfTemp['qty_shipped'] = $helper->getPdfQtyAsInt() ? (int)$item->getQtyShipped() : $item->getQtyShipped();
            } else {
                $isOrderItem = false;
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $pdfTemp['qty'] = $helper->getPdfQtyAsInt() ? (int)$item->getQty() : $item->getQty();
                $pdfTemp['qty_ordered'] = $helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyOrdered() : $orderItem->getQtyOrdered();
                $pdfTemp['qty_backordered'] = $helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyBackordered() : $orderItem->getQtyBackordered();
                $pdfTemp['qty_shipped'] = $helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyShipped() : $orderItem->getQtyShipped();
            }
            $pdfTemp['qty_stock'] = '';
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($orderItem->getProductId());
            if ($stockItem) {
                $pdfTemp['qty_stock'] = $helper->getPdfQtyAsInt() ? (int)$stockItem->getQty() : $stockItem->getQty();
            }
            $pdfTemp['qty_detailed'][] = Mage::helper('sales')->__('Ordered') . ' ' . $pdfTemp['qty_ordered'];
            if ($orderItem->getQtyInvoiced()> 0.0001) {
                $pdfTemp['qty_detailed'][] = Mage::helper('sales')->__('Invoiced') . ' ' .
                    ($helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyInvoiced() : $orderItem->getQtyInvoiced());
            }
            if ($orderItem->getQtyShipped()> 0.0001) {
                $pdfTemp['qty_detailed'][] = Mage::helper('sales')->__('Shipped') . ' ' .
                    ($helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyShipped() : $orderItem->getQtyShipped());
            }
            if ($orderItem->getQtyRefunded()> 0.0001) {
                $pdfTemp['qty_detailed'][] = Mage::helper('sales')->__('Refunded') . ' ' .
                    ($helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyRefunded() : $orderItem->getQtyRefunded());
            }
            if ($orderItem->getQtyCanceled() > 0.0001) {
                $pdfTemp['qty_detailed'][] = Mage::helper('sales')->__('Canceled') . ' ' .
                    ($helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyCanceled() : $orderItem->getQtyCanceled());
            }
            if ($orderItem->getQtyBackordered() > 0.0001) {
                $pdfTemp['qty_detailed'][] = Mage::helper('sales')->__('Backordered') . ' ' .
                    ($helper->getPdfQtyAsInt() ? (int)$orderItem->getQtyBackordered() : $orderItem->getQtyBackordered());
            }
            $pdfTemp['item_status'] = $orderItem->getStatus();
            //we generally don't want to display subitems of configurable products etc but we do for bundled
            $type = $orderItem->getProductType();
            $itemId = $orderItem->getItemId();
            $parentType = 'none';
            $parentItemId = $orderItem->getParentItemId();
            $parentProductId = false;

            if ($parentItemId) {
                $parentItem = Mage::getModel('sales/order_item')->load($parentItemId);
                $parentType = $parentItem->getProductType();
                $parentProductId = $parentItem->getProductId();
            }

            //Get item details
            $pdfTemp['itemId'] = $itemId;
            $pdfTemp['productId'] = $orderItem->getProductId();
            $pdfTemp['type'] = $type;
            $pdfTemp['parentType'] = $parentType;
            $pdfTemp['parentItemId'] = $parentItemId;
            $pdfTemp['productDetails'] = $this->getItemNameAndSku($item, $helper);
            $pdfTemp['productOptions'] = $orderItem->getProductOptions();
            $pdfTemp['giftMessage'] = $this->getGiftMessage($orderItem);
            if ($displayItemTaxInclusive) {
                if ($item->getPriceInclTax()) {
                    $pdfTemp['price'] = $item->getPriceInclTax();
                } elseif ($pdfTemp['qty']) {
                    $pdfTemp['price']
                        = $item->getPrice() + ($item->getTaxAmount() + $item->getHiddenTaxAmount()) / $pdfTemp['qty'];
                } else {
                    $pdfTemp['price'] = $item->getPrice();
                }
            } else {
                $pdfTemp['price'] = $item->getPrice();
            }
            $pdfTemp['discountAmount'] = $item->getDiscountAmount();

            $pdfTemp['taxAmount'] = $item->getTaxAmount();
            if ($displayTaxInclusiveHiddenTaxAmount) {
                $pdfTemp['taxAmount'] += $item->getHiddenTaxAmount();
            }
            $pdfTemp['rowTotal2'] = $item->getRowTotal();
            if ($displayItemTaxInclusive || $displaySubtotalTaxInclusive) {
                if ($item->getRowTotalInclTax()) {
                    $pdfTemp['rowTotal2']
                        += $item->getTaxAmount();
                    if ($displayTaxAfterDiscount) {
                        $pdfTemp['rowTotal2'] += $item->getHiddenTaxAmount();
                    }
                } else {
                    $pdfTemp['rowTotal2'] += $item->getTaxAmount();
                }
            }
            if ($item->getRowTotalInclTax()) {
                $pdfTemp['rowTotal2'] -= $pdfTemp['discountAmount'];
                $pdfTemp['subtotal'] = $item->getRowTotalInclTax();
            } else {
                $pdfTemp['subtotal'] = $item->getRowTotal() + $item->getTaxAmount();
            }

            if (!$displaySubtotalTaxInclusive) {
                $pdfTemp['subtotal'] -= $item->getTaxAmount();
            }

            if (Mage::helper('tax')->displaySalesPriceInclTax()) {
                $pdfTemp['subtotal2'] = Mage::helper('checkout')->getSubtotalInclTax($item);
            } else {
                $pdfTemp['subtotal2'] = $item->getRowTotal();
            }

            if ($displayFptInclusivePrices) {
                $pdfTemp['weee'] = Mage::helper('weee')->getApplied($item);
                if (empty($pdfTemp['weee'])) {
                    $pdfTemp['weee'] = false;
                }
                $pdfTemp['price'] += $item->getWeeeTaxAppliedAmount();
                $pdfTemp['rowTotal2'] += $item->getWeeeTaxAppliedAmount();
                $pdfTemp['subtotal'] += $item->getWeeeTaxAppliedAmount();
            } else {
                $pdfTemp['weee'] = false;
            }

            //get item details - BASE
            if ($displayItemTaxInclusive) {
                if ($item->getBasePriceInclTax()) {
                    $pdfTemp['basePrice'] = $item->getBasePriceInclTax();
                } elseif ($pdfTemp['qty']) {
                    $pdfTemp['basePrice'] =
                        $item->getBasePrice()
                            + ($item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount()) / $pdfTemp['qty'];
                } else {
                    $pdfTemp['basePrice'] = $item->getBasePrice();
                }
            } else {
                $pdfTemp['basePrice'] = $item->getBasePrice();
            }
            $pdfTemp['baseDiscountAmount'] = $item->getBaseDiscountAmount();
            $pdfTemp['baseTaxAmount'] = $item->getBaseTaxAmount();
            if ($displayTaxInclusiveHiddenTaxAmount) {
                $pdfTemp['baseTaxAmount'] += $item->getBaseHiddenTaxAmount();
            }
            $pdfTemp['baseRowTotal2'] = $item->getBaseRowTotal();
            if ($displayItemTaxInclusive || $displaySubtotalTaxInclusive) {
                if ($item->getRowTotalInclTax()) {
                    $pdfTemp['baseRowTotal2'] += $item->getBaseTaxAmount();
                    if ($displayTaxAfterDiscount) {
                        $pdfTemp['baseRowTotal2'] += $item->getBaseHiddenTaxAmount();
                    }
                } else {
                    $pdfTemp['baseRowTotal2'] += $item->getBaseTaxAmount();
                }
            }

            if ($item->getBaseRowTotalInclTax()) {
                $pdfTemp['baseRowTotal2'] -= $pdfTemp['baseDiscountAmount'];
                $pdfTemp['baseSubtotal'] = $item->getBaseRowTotalInclTax();
            } else {
                $pdfTemp['baseSubtotal'] = $item->getBaseRowTotal() + $item->getBaseTaxAmount();
            }

            if (!$displaySubtotalTaxInclusive) {
                $pdfTemp['baseSubtotal'] -= $item->getBaseTaxAmount();
            }

            if (Mage::helper('tax')->displaySalesPriceInclTax()) {
                $pdfTemp['baseSubtotal2'] = Mage::helper('checkout')->getBaseSubtotalInclTax($item);
            } else {
                $pdfTemp['baseSubtotal2'] = $item->getBaseRowTotal();
            }

            if ($displayFptInclusivePrices) {
                $pdfTemp['basePrice'] += $item->getBaseWeeeTaxAppliedAmount();
                $pdfTemp['baseRowTotal2'] += $item->getBaseWeeeTaxAppliedAmount();
                $pdfTemp['baseSubtotal'] += $item->getBaseWeeeTaxAppliedAmount();
            }

            if ($orderItem->getTaxPercent()) {
                $taxPercent = sprintf("%01.4f", $orderItem->getTaxPercent());
            } else {
                $taxPercent = '0.000';
            }
            $pdfTemp['taxPercent'] = sprintf("%01.2f", $taxPercent) . '%';
            $parentFixedPrice = false;
            if ($type == 'bundle') {
                $bundlePdfModel = Mage::getModel('bundle/sales_order_pdf_items_invoice');
                $bundlePdfModel->setItem($orderItem);
                $parentFixedPrice = $bundlePdfModel->isChildCalculated($orderItem);
                if ($parentFixedPrice) {
                    $this->trackItemTax($item, $taxPercent);
                }
            } elseif ($parentType != 'bundle') {
                $this->trackItemTax($item, $taxPercent);
            }

            //prepare image
            $pdfTemp['image'] = false;
            if ($helper->printProductImages()) {
                $infoBuyRequest = $orderItem->getProductOptionByCode('info_buyRequest');
                if (isset($infoBuyRequest['zetaprints-downloaded-previews'])) {
                    $pdfTemp['image'] = current($infoBuyRequest['zetaprints-downloaded-previews']);
                } else {
                    $pdfTemp['image'] = $this->prepareProductImage(
                        $pdfTemp['productId'],
                        $pdfTemp['productDetails']['Sku'],
                        $parentProductId
                    );
                }
            }

            //collect bundle subitems separately
            if ($type == 'bundle') {
                $bundleHelper = Mage::helper('pdfcustomiser/bundle');

                if ($isOrderItem) {
                    $bundleChildren = $orderItem->getChildrenItems();
                } else {
                    $bundleChildren = $bundleHelper->getChilds($item);
                }

                if ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
                    $shipSeparate = $bundleHelper->isShipmentSeparately($item);
                } else {
                    $shipSeparate = false;
                }
                foreach ($bundleChildren as $childItem) {
                    if ($childItem->getId() == $item->getId()) {
                        continue;
                    }
                    $selectionAttributes = $bundleHelper->getSelectionAttributes($childItem);
                    $subBundleItem = array();
                    $subBundleItem['price'] = $childItem->getPrice();
                    $subBundleItem['parentItemId'] = $itemId;
                    if (!($childItem instanceof Mage_Sales_Model_Order_Item)) {
                        $childOrderItem = Mage::getModel('sales/order_item')->load($childItem->getOrderItemId());
                    } else {
                        $childOrderItem = $childItem;
                    }

                    if ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
                        if ($shipSeparate) {
                            $subBundleItem['qty'] =
                                $helper->getPdfQtyAsInt()
                                    ? ((int)$item->getQty()*$selectionAttributes['qty'])
                                    : ($item->getQty()*$selectionAttributes['qty']);
                        } else {
                            $subBundleItem['qty'] = $helper->getPdfQtyAsInt()
                                ? (int)$childItem->getQty()
                                : $childItem->getQty();
                        }
                        if (!$shipSeparate || $parentFixedPrice) {
                            $pdfTemp['qty'] = '';
                        }
                    } else {
                        if ($parentFixedPrice) {
                            $subBundleItem['qty'] = $helper->getPdfQtyAsInt()
                                ? (int)$selectionAttributes['qty']
                                : $selectionAttributes['qty'];
                        } elseif ($isOrderItem) {
                            $subBundleItem['qty'] = $helper->getPdfQtyAsInt()
                                ? (int)$childItem->getQtyOrdered()
                                : $childItem->getQtyOrdered();
                        } else {
                            $subBundleItem['qty'] = $helper->getPdfQtyAsInt()
                                ? (int)$childItem->getQty()
                                : $childItem->getQty();
                        }
                    }

                    if ($displayItemTaxInclusive) {
                        if ($childItem->getPriceInclTax()) {
                            $subBundleItem['price'] = $childItem->getPriceInclTax();
                        } elseif ($subBundleItem['qty']) {
                            $subBundleItem['price']
                                = $childItem->getPrice() + ($childItem->getTaxAmount() + $childItem->getHiddenTaxAmount()) / $subBundleItem['qty'];
                        } else {
                            $subBundleItem['price'] = $childItem->getPrice();
                        }
                    } else {
                        $subBundleItem['price'] = $childItem->getPrice();
                    }
                    $subBundleItem['discountAmount'] = $childOrderItem->getDiscountAmount();
                    $subBundleItem['qty_ordered'] = $helper->getPdfQtyAsInt() ? (int)$childOrderItem->getQtyOrdered() : $childOrderItem->getQtyOrdered();
                    $subBundleItem['qty_backordered'] = $helper->getPdfQtyAsInt() ? (int)$childOrderItem->getQtyBackordered() : $childOrderItem->getQtyBackordered();

                    $subBundleItem['taxAmount'] = $childItem->getTaxAmount();
                    if ($displayTaxInclusiveHiddenTaxAmount) {
                        $subBundleItem['taxAmount'] += $childItem->getHiddenTaxAmount();
                    }
                    $subBundleItem['rowTotal2'] = $childItem->getRowTotal();

                    if ($displayItemTaxInclusive || $displaySubtotalTaxInclusive) {
                        if ($childItem->getRowTotalInclTax()) {
                            $subBundleItem['rowTotal2'] += $childItem->getTaxAmount();
                            if ($displayTaxAfterDiscount) {
                                $subBundleItem['rowTotal2'] += $childItem->getHiddenTaxAmount();
                            }
                        } else {
                            $subBundleItem['rowTotal2'] += $childItem->getTaxAmount();
                        }
                    }

                    if ($childItem->getRowTotalInclTax()) {
                        $subBundleItem['rowTotal2'] -= $subBundleItem['discountAmount'];
                        $subBundleItem['subtotal'] = $childItem->getRowTotalInclTax();
                    } else {
                        $subBundleItem['subtotal'] = $childItem->getRowTotal() + $childItem->getTaxAmount();
                    }

                    if (!$displaySubtotalTaxInclusive) {
                        $subBundleItem['subtotal'] -= $childItem->getTaxAmount();
                    }

                    if (Mage::helper('tax')->displaySalesPriceInclTax()) {
                        $subBundleItem['subtotal2'] = Mage::helper('checkout')->getSubtotalInclTax($childItem);
                    } else {
                        $subBundleItem['subtotal2'] = $childItem->getRowTotal();
                    }

                    if ($displayFptInclusivePrices) {
                        $subBundleItem['weee'] = Mage::helper('weee')->getApplied($childItem);
                        if (empty($subBundleItem['weee'])) {
                            $subBundleItem['weee'] = false;
                        }
                        $subBundleItem['price'] += $childItem->getWeeeTaxAppliedAmount();
                        $subBundleItem['rowTotal2'] += $childItem->getWeeeTaxAppliedAmount();
                        $subBundleItem['subtotal'] += $childItem->getWeeeTaxAppliedAmount();
                    } else {
                        $subBundleItem['weee'] = false;
                    }

                    //get item details - BASE
                    if ($displayItemTaxInclusive) {
                        if ($item->getBasePriceInclTax()) {
                            $subBundleItem['basePrice'] = $childItem->getBasePriceInclTax();
                        } elseif ($subBundleItem['qty']) {
                            $subBundleItem['basePrice'] =
                                $childItem->getBasePrice()
                                + ($childItem->getBaseTaxAmount() + $childItem->getBaseHiddenTaxAmount()) / $subBundleItem['qty'];
                        } else {
                            $subBundleItem['basePrice'] = $childItem->getBasePrice();
                        }
                    } else {
                        $subBundleItem['basePrice'] = $childItem->getBasePrice();
                    }
                    $subBundleItem['baseDiscountAmount'] = $childItem->getBaseDiscountAmount()?$childItem->getBaseDiscountAmount():$childOrderItem->getBaseDiscountAmount();

                    $subBundleItem['baseTaxAmount'] = $childItem->getBaseTaxAmount();
                    if ($displayTaxInclusiveHiddenTaxAmount) {
                        $subBundleItem['baseTaxAmount'] += $childItem->getBaseHiddenTaxAmount();
                    }
                    $subBundleItem['baseRowTotal2'] = $childItem->getBaseRowTotal();

                    if ($displayItemTaxInclusive || $displaySubtotalTaxInclusive) {
                        if ($childItem->getRowTotalInclTax()) {
                            $subBundleItem['baseRowTotal2'] += $childItem->getBaseTaxAmount();
                            if ($displayTaxAfterDiscount) {
                                $subBundleItem['baseRowTotal2'] += $childItem->getBaseHiddenTaxAmount();
                            }
                        } else {
                            $subBundleItem['baseRowTotal2'] += $childItem->getBaseTaxAmount();
                        }
                    }


                    if ($childItem->getBaseRowTotalInclTax()) {
                        $subBundleItem['baseRowTotal2'] -= $subBundleItem['baseDiscountAmount'];
                        $subBundleItem['baseSubtotal'] = $childItem->getBaseRowTotalInclTax();
                    } else {
                        $subBundleItem['baseSubtotal'] = $childItem->getBaseRowTotal() + $childItem->getBaseTaxAmount();
                    }

                    if (!$displaySubtotalTaxInclusive) {
                        $subBundleItem['baseSubtotal'] -= $childItem->getBaseTaxAmount();
                    }

                    if (Mage::helper('tax')->displaySalesPriceInclTax()) {
                        $subBundleItem['baseSubtotal2'] = Mage::helper('checkout')->getBaseSubtotalInclTax($childItem);
                    } else {
                        $subBundleItem['baseSubtotal2'] = $childItem->getBaseRowTotal();
                    }

                    if ($displayFptInclusivePrices) {
                        $subBundleItem['basePrice'] += $childItem->getBaseWeeeTaxAppliedAmount();
                        $subBundleItem['baseRowTotal2'] += $childItem->getBaseWeeeTaxAppliedAmount();
                        $subBundleItem['baseSubtotal'] += $childItem->getBaseWeeeTaxAppliedAmount();
                    }

                    if ($childOrderItem->getTaxPercent()) {
                        $taxPercent = sprintf("%01.4f", $childOrderItem->getTaxPercent());
                    } else {
                        $taxPercent = '0.0000';
                    }

                    if (!$parentFixedPrice) {
                        $this->trackItemTax($childItem, $taxPercent, $orderItem->getTaxPercent());
                    }

                    $subBundleItem['taxPercent'] = $taxPercent;
                    if ($helper->printProductImages()) {
                        $subBundleItem['image'] = $this->prepareProductImage(
                            $childItem->getProductId(),
                            false
                        );
                    }

                    $subBundleItem['productDetails'] = $this->getItemNameAndSku($childItem, $helper);
                    if ($selectionAttributes['option_label']) {
                        $subBundleItem['productDetails']['Name'] = "<b>".$selectionAttributes['option_label']."</b>: "
                            . $subBundleItem['productDetails']['Name'];
                    }
                    $transport = new Varien_Object();
                    $transport->setItemData($subBundleItem);
                    Mage::dispatchEvent(
                        'fooman_pdfcustomiser_prepare_subbundleitem',
                        array(
                             'item'=> $childItem,
                             'transport' => $transport
                        )
                    );
                    $this->_pdfBundleItems[$itemId][] = $transport->getItemData();
                }
            }
            if ($parentType != 'bundle') {
                $transport = new Varien_Object();
                $transport->setItemData($pdfTemp);
                Mage::dispatchEvent(
                    'fooman_pdfcustomiser_prepare_item',
                    array(
                        'item'=> $item,
                        'transport' => $transport
                    )
                );
                $this->_pdfItems[$itemId] = $transport->getItemData();
            }
        }
        $this->_sortBy = $helper->getColumnsSortOrder();
        if ($this->_sortBy) {
            uasort($this->_pdfItems, array($this, 'cmp'));
            //uasort($this->_pdfBundleItems, array($this, 'cmp'));
        }
        $this->prepareTotalTaxes($order, $printItem, $helper);
    }

    /**
     * add totals to the tax summaries
     * work out what tax rates were used
     *
     * @param $order
     * @param $printItem
     * @param $helper
     *
     * @return void
     */
    public function prepareTotalTaxes($order, $printItem, $helper)
    {
        $this->shippingTaxRate = 0;
        $processedShipping = false;

        $this->surchargeTaxRate = 0;
        $processedSurcharge = false;

        $this->giftwrappingTaxRate = 0;
        $processedGiftwrapping = false;

        $this->cashOnDeliveryTaxRate = 0;
        $processedCashOnDelivery = false;

        $giftWrappingTaxes = $printItem->getGwBaseTaxAmount() + $printItem->getGwItemsBaseTaxAmount()
            + $printItem->getGwCardBaseTaxAmount();

        if ($helper->displayTaxSummary() && $order) {
            //$filteredTaxrates = Mage::helper('pdfcustomiser')->getCalculatedTaxes($printItem, true);
            if ($this->getTaxTotal()) {
                $combinedTaxPercentage = 0;
                //loop over tax amounts to find the tax rate applied to shipping
                foreach ($this->getTaxTotal() as $taxPercent => $amount) {
                    if ($taxPercent == 0) {
                        continue;
                    }
                    $combinedTaxPercentage += $taxPercent;
                    //Magento keeps no record of the tax rate for shipping
                    //due to rounding we can only get to within
                    //reasonable approximation of the rate
                    if (!$processedShipping) {
                        $processedShipping = $this->checkTaxTotal(
                            $printItem->getBaseShippingAmount(),
                            $printItem->getBaseShippingTaxAmount(),
                            $taxPercent,
                            $this->shippingTaxRate,
                            $combinedTaxPercentage
                        );
                    }

                    if (!$processedSurcharge) {
                        $processedSurcharge = $this->checkTaxTotal(
                            $printItem->getBaseFoomanSurchargeAmount(),
                            $printItem->getBaseFoomanSurchargeTaxAmount(),
                            $taxPercent,
                            $this->surchargeTaxRate,
                            $combinedTaxPercentage
                        );
                    }

                    if (!$processedCashOnDelivery) {
                        $processedCashOnDelivery = $this->checkTaxTotal(
                            $printItem->getBaseCodFee(),
                            $printItem->getBaseCodTaxAmount(),
                            $taxPercent,
                            $this->cashOnDeliveryTaxRate,
                            $combinedTaxPercentage
                        );
                    }

                    if (!$processedGiftwrapping) {
                        $processedGiftwrapping = $this->checkTaxTotal(
                            $printItem->getGwBasePrice() + $printItem->getGwItemsBasePrice()
                                + $printItem->getGwCardBasePrice(),
                            $giftWrappingTaxes,
                            $taxPercent,
                            $this->giftwrappingTaxRate,
                            $combinedTaxPercentage
                        );
                    }
                }
            }
        }

        if (
            (!$processedShipping && $printItem->getBaseShippingTaxAmount() != 0)
            || (!$processedSurcharge && $printItem->getBaseFoomanSurchargeTaxAmount() != 0)
            || (!$processedCashOnDelivery && $printItem->getBaseCodFee() != 0)
            || (!$processedGiftwrapping && $giftWrappingTaxes != 0)
        ) {
            foreach ($order->getFullTaxInfo() as $taxRate) {
                $combinedTaxPercentage = 0;
                foreach ($taxRate['rates'] as $rate) {
                    $combinedTaxPercentage += $rate['percent'];
                    if (!$processedShipping) {
                        $processedShipping = $this->checkTaxTotal(
                            $printItem->getBaseShippingAmount(),
                            $printItem->getBaseShippingTaxAmount(),
                            $rate['percent'],
                            $this->shippingTaxRate,
                            $combinedTaxPercentage
                        );
                    }
                    if (!$processedSurcharge) {
                        $processedSurcharge = $this->checkTaxTotal(
                            $printItem->getBaseFoomanSurchargeAmount(),
                            $printItem->getBaseFoomanSurchargeTaxAmount(),
                            $rate['percent'],
                            $this->surchargeTaxRate,
                            $combinedTaxPercentage
                        );
                    }
                    if (!$processedCashOnDelivery) {
                        $processedCashOnDelivery = $this->checkTaxTotal(
                            $printItem->getBaseCodFee(),
                            $printItem->getBaseCodTaxAmount(),
                            $rate['percent'],
                            $this->cashOnDeliveryTaxRate,
                            $combinedTaxPercentage
                        );
                    }

                    if (!$processedGiftwrapping) {
                        $processedGiftwrapping = $this->checkTaxTotal(
                            $printItem->getGwBasePrice() + $printItem->getGwItemsBasePrice()
                            + $printItem->getGwCardBasePrice(),
                            $giftWrappingTaxes,
                            $rate['percent'],
                            $this->giftwrappingTaxRate,
                            $combinedTaxPercentage
                        );
                    }
                }
            }
        }
        $zero = sprintf("%01.4f", 0);
        if ( !$processedShipping &&
            abs($this->shippingTaxRate) < 0.005 && $printItem->getBaseShippingAmount() != 0) {
            $this->addAmountsToTaxTotals(
                $zero,
                $printItem->getBaseShippingTaxAmount(),
                $printItem->getBaseShippingAmount()
            );
        }
        if (!$processedSurcharge &&
            abs($this->surchargeTaxRate) < 0.005 && $printItem->getBaseFoomanSurchargeAmount() != 0) {
            $this->addAmountsToTaxTotals(
                $zero,
                $printItem->getBaseFoomanSurchargeTaxAmount(),
                $printItem->getBaseFoomanSurchargeAmount()
            );
        }

        if (!$processedCashOnDelivery &&
            abs($this->cashOnDeliveryTaxRate) < 0.005 && $printItem->getBaseCodFee() != 0) {
            $this->addAmountsToTaxTotals(
                $zero,
                $printItem->getBaseCodFee(),
                $printItem->getBaseCodTaxAmount()
            );
        }

        if (!$processedGiftwrapping &&
            abs($this->giftwrappingTaxRate) < 0.005 && $printItem->getGwBaseTaxAmount() + $printItem->getGwItemsBaseTaxAmount() + $printItem->getGwCardBaseTaxAmount() != 0) {
            $this->addAmountsToTaxTotals(
                $zero,
                $printItem->getGwBaseTaxAmount() + $printItem->getGwItemsBaseTaxAmount() + $printItem->getGwCardBaseTaxAmount(),
                $printItem->getGwBasePrice() + $printItem->getGwItemsBasePrice() + $printItem->getGwCardBasePrice()
            );
        }

        if ($printItem instanceof Mage_Sales_Model_Order_Creditmemo && $printItem->getBaseAdjustmentNegative() > 0) {
            $this->addAmountsToTaxTotals(
                $zero,
                -$printItem->getBaseAdjustmentNegative(),
                -$printItem->getBaseAdjustmentNegative()
            );
        }

        if ($printItem instanceof Mage_Sales_Model_Order_Creditmemo && $printItem->getBaseAdjustmentPositive() > 0) {
            $this->addAmountsToTaxTotals(
                $zero,
                $printItem->getBaseAdjustmentPositive(),
                $printItem->getBaseAdjustmentPositive()
            );
        }
    }

    public function addAmountsToTaxTotals($percentage, $taxAmount, $taxTotal)
    {
        if (isset($this->_taxTotal[$percentage])) {
            $this->_taxTotal[$percentage] += $taxTotal;
        } else {
            $this->_taxTotal[$percentage] = $taxTotal;
        }
        if (isset($this->_taxAmount[$percentage])) {
            $this->_taxAmount[$percentage] += $taxAmount;
        } else {
            $this->_taxAmount[$percentage] = $taxAmount;
        }
    }

    /**
     * retrieve list of prepared line items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_pdfItems;
    }

    /**
     * set list of prepared line items
     *
     * @param $items
     */
    public function setItems($items)
    {
        $this->_pdfItems = $items;
    }

    /**
     * retrieve list of prepared bundled items
     *
     * @return array
     */
    public function getBundleItems()
    {
        return $this->_pdfBundleItems;
    }

    /**
     * retrieve collected tax total, split by tax rate
     *
     * @return array
     */
    public function getTaxTotal()
    {
        return $this->_taxTotal;
    }

    /**
     * retrieve tax amounts paid, split by tax rate
     *
     * @return array
     */
    public function getTaxAmount()
    {
        return $this->_taxAmount;
    }

    /**
     * add header to new page, includes logo
     * sets final height of logo on helper object
     * optionally print the incrementId as barcode
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $title
     * @param bool                            $incrementId
     */
    public function printHeader(Fooman_PdfCustomiser_Helper_Pdf $helper, $title, $incrementId = false, $hideLogo)
    {

        if ($incrementId) {
            $style = array('text' => false);
            $this->write1DBarcode(
                $incrementId,
                $helper->getBarcodeType(),
                $helper->getPdfMargins('sides'),
                5,
                50,
                5,
                '',
                $style
            );
            $this->SetXY($helper->getPdfMargins('sides'), $helper->getPdfMargins('top'));
        }
        // Place Logo
        if ($helper->getPdfLogo() && !$hideLogo) {
            if ($helper->getPdfLogoPlacement() == 'auto-right') {
                $maxLogoHeight = 25;
                $currentY = $this->GetY();
                //Figure out if logo is too wide - half the page width minus margins
                $maxWidth = ($helper->getPageWidth() / 2) - $helper->getPdfMargins('sides');
                if ($helper->getPdfLogoDimensions('w') > $maxWidth) {
                    $logoWidth = $maxWidth;
                } else {
                    $logoWidth = $helper->getPdfLogoDimensions('w');
                }
                //centered
                /*
                $this->Image(
                    $helper->getPdfLogo(),
                    $this->getPageWidth()/2 +(($this->getPageWidth()/ -$helper->getPdfMargins('sides')-$logoWidth)/2),
                    $helper->getPdfMargins('top'),
                    $logoWidth,
                    $maxLogoHeight,
                    $type='',
                    $link='',
                    $align='',
                    $resize=false,
                    $dpi=300,
                    $palign='',
                    $ismask=false,
                    $imgmask=false,
                    $border=0,
                    $fitbox=true
                );*/
                $this->Image(
                    $helper->getPdfLogo(),
                    $this->getPageWidth() / 2,
                    $helper->getPdfMargins('top'),
                    $logoWidth,
                    $maxLogoHeight,
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = true
                );
                $helper->setImageHeight($this->getImageRBY() + 3 - $currentY);
            } elseif ($helper->getPdfLogoPlacement() == 'auto') {
                $maxLogoHeight = 25;
                $currentY = $this->GetY();

                //Figure out if logo is too wide - half the page width minus margins
                $maxWidth = ($helper->getPageWidth() / 2) - $helper->getPdfMargins('sides');
                if ($helper->getPdfLogoDimensions('w') > $maxWidth) {
                    $logoWidth = $maxWidth;
                } else {
                    $logoWidth = $helper->getPdfLogoDimensions('w');
                }
                //centered
                /*
                $this->Image(
                    $helper->getPdfLogo(),
                    $this->getPageWidth()/2 +(($this->getPageWidth()/2-$helper->getPdfMargins('sides')-$logoWidth)/2),
                    $helper->getPdfMargins('top'),
                    $logoWidth,
                    $maxLogoHeight,
                    $type='',
                    $link='',
                    $align='',
                    $resize=false,
                    $dpi=300,
                    $palign='',
                    $ismask=false,
                    $imgmask=false,
                    $border=0,
                    $fitbox=true
                );*/
                $this->Image(
                    $helper->getPdfLogo(),
                    $helper->getPdfMargins('sides'),
                    $helper->getPdfMargins('top'),
                    $logoWidth,
                    $maxLogoHeight,
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = true
                );
                $helper->setImageHeight($this->getImageRBY() + 3 - $currentY);
            } elseif ($helper->getPdfLogoPlacement() == 'no-scaling-right') {
                $currentY = $this->GetY();
                $this->Image(
                    $helper->getPdfLogo(),
                    $this->getPageWidth() / 2, $helper->getPdfMargins('top'),
                    $helper->getPdfLogoDimensions('w'),
                    $helper->getPdfLogoDimensions('h'),
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = false
                );
                $helper->setImageHeight($this->getImageRBY() + 3 - $currentY);
            } elseif ($helper->getPdfLogoPlacement() == 'no-scaling') {
                $currentY = $this->GetY();
                $this->Image(
                    $helper->getPdfLogo(),
                    $helper->getPdfMargins('sides'),
                    $helper->getPdfMargins('top'),
                    $helper->getPdfLogoDimensions('w'),
                    $helper->getPdfLogoDimensions('h'),
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = false
                );
                $helper->setImageHeight($this->getImageRBY() + 3 - $currentY);
            } else {
                $currentY = $this->GetY();
                $coords = $helper->getPdfLogoCoords();
                $this->Image(
                    $helper->getPdfLogo(),
                    $coords['x'],
                    $coords['y'],
                    $coords['w'] * self::FACTOR_PIXEL_PER_MM, $coords['h'] * self::FACTOR_PIXEL_PER_MM,
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = true
                );
                $helper->setImageHeight($this->getImageRBY() + 3 - $currentY);
            }
        } else {
            $helper->setImageHeight(false);
        }
    }

    /**
     * set some standards for all pdf pages
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     */
    public function SetStandard(Fooman_PdfCustomiser_Helper_Pdf $helper)
    {

        // set document information
        $this->SetCreator('Magento');
        $this->tcpdflink = false;
        //$this->setPDFVersion('1.4');

        //set margins
        $this->SetMargins($helper->getPdfMargins('sides'), $helper->getPdfMargins('top'));

        // set header and footer
        $printNumbers = Mage::getStoreConfigFlag('sales_pdf/all/allpagenumbers', $helper->getStoreId());
        $this->setPrintFooter($printNumbers || $helper->hasFooter());

        if ($helper->getPdfIntegratedLabels()) {
            //uncomment next line to suppress the footers when using integrated labels
            //$this->setPrintFooter(false);
        }

        $this->setPrintHeader(true);

        $this->setHeaderMargin(0);
        $this->setFooterMargin($helper->getPdfMargins('bottom'));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set auto page breaks
        $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());

        //set image scale factor 3 pixels = 1mm
        $this->setImageScale(self::FACTOR_PIXEL_PER_MM);

        //set image quality
        $this->setJPEGQuality(95);

        //comment for smaller file sizes when not using core fonts
        //downside is larger processing time and might be needed to work around a limitation in IOS
        //default pdf reader
        $this->setFontSubsetting(false);

        // set font
        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());

        // set fillcolor black
        $this->SetFillColor(0);

        // see if we need to sign
        if (Mage::getStoreConfig('sales_pdf/all/allsign')) {
            $certificate = Mage::helper('core')->decrypt(Mage::getStoreConfig('sales_pdf/all/allsigncertificate'));
            $certpassword = Mage::helper('core')->decrypt(Mage::getStoreConfig('sales_pdf/all/allsignpassword'));

            // set document signature
            $this->setSignature($certificate, $certificate, $certpassword, '', 2);
        }

        //set Right to Left Language
        if (
            Mage::app()->getLocale()->getLocaleCode() == 'he_IL'
            || Mage::app()->getLocale()->getLocaleCode() == 'ar_SA'
        ) {
            $this->setRTL(true);
            $helper->setParameter(0, 'rtl', true);
        } else {
            $this->setRTL(false);
            $helper->setParameter(0, 'rtl', false);
        }
        $this->startPageGroup();

    }

    /**
     * Header function called immediately after a new page is added
     * used to output background images
     *
     * @param void
     */
    public function Header()
    {
        $helper = $this->getPdfHelper();
        $helper->setStoreId($this->getStoreId());
        if (!$helper->getPdfBgOnlyFirst()
            || ($helper->getPdfBgOnlyFirst() && isset($this->newpagegroup[$this->page]))
        ) {
            $imagePath = $helper->getPdfBgImage();
            if (file_exists($imagePath) && !$helper->getHideBackground()) {
                $this->SetAutoPageBreak(false);
                $this->Image(
                    $imagePath,
                    0,
                    0,
                    $this->getPageWidth(),
                    $this->getPageHeight(),
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = true,
                    $hidden = false
                );
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
            }
        }
        // Line break
        $this->Ln();

    }

    public function Footer()
    {
        $helper = $this->getPdfHelper();
        $helper->setStoreId($this->getStoreId());
        $footers = $helper->getFooters();

        if ($footers[0] > 0) {
            $marginBetween = 5;
            $width = ($this->getPageWidth() - 2 * $helper->getPdfMargins('sides') - ($footers[0] - 1) * $marginBetween)
                / $footers[0];
            $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize('small'));
            $block = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block', 'pdfcustomiser.footer');

            $html = $block->setPdfHelper($helper)
                ->setPdf($this)
                ->setWidth($width)
                ->setMarginBetween($marginBetween)
                ->setFooters($footers)
                ->setTemplate('fooman/pdfcustomiser/footer.phtml')
                ->toHtml();

            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            $processor->setVariables(
                array(
                     'sales_object'    => $helper->getSalesObject(),
                )
            );
            $html = $processor->filter($html);

            $this->SetAutoPageBreak(false);
            $this->writeHTML($html);
            $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize(''));
            $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
        }
        if (Mage::getStoreConfig('sales_pdf/all/allpagenumbers', $helper->getStoreId())) {
            $this->MultiCell(0, 0, $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias(), 0, 'C', 0, 1);
            /*
            $this->MultiCell(
                ($this->getPageWidth()- 2* $helper->getPdfMargins('sides'))/2,
                0,
                $this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias(),
                0,
                'L',
                0,
                0
            );
            $this->MultiCell(0, 0, $this->getIncrementId(), 0, 'R', 0, 1);
            */
        }
    }

    /**
     * draw a line within the margins of the page
     * leaving space above and below
     *
     * @param int $space
     */
    public function Line2($space = 1)
    {
        $this->SetY($this->GetY() + $space);
        $margins = $this->getMargins();
        $this->Line($margins['left'], $this->GetY(), $this->getPageWidth() - $margins['right'], $this->GetY());
        $this->SetY($this->GetY() + $space);

    }

    /**
     * get product name and Sku,
     * take into consideration configurable products and product options
     *
     * @param $item
     * @param $helper
     *
     * @return array
     */
    public function getItemNameAndSku($item, $helper)
    {
        $return = array();
        $return['Name'] = $helper->fixEncoding($item->getName());
        $return['Sku'] = $helper->fixEncoding($item->getSku());
        //$return['Name'] = $item->getName();
        //$return['Sku'] = $item->getSku();
        $return['Subitems'] = false;

        //check if we are printing an non-order = item has a method getOrderItem
        if ($item->getOrderItemId()) {
            $item = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
        }
        $return['Options'] = $item->getProductOptions();

        if ($return['Options']) {
            if ($item->getProductOptionByCode('simple_sku')) {
                $return['Sku'] = $item->getProductOptionByCode('simple_sku');
            }

            /*
            //uncomment to use the sku of the parent configurable product instead
            if ($item->getProductType() == 'configurable') {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if ($product->getSku()) {
                    $return['Sku'] = $product->getSku();
                }
            }
            */

            $return['Options'] = str_replace('::', ':', $return['Options']);
        }

        $return['custom'] = '';
        for ($i = 2; $i <= 5; $i++) {
            $return['custom' . $i] = '';
        }

        $attributeCodes = $helper->getCustomColumnAttributes();
        if ($attributeCodes) {
            //load the product via sku here, will display the custom attribute of the
            //simple product attached to a configurable

            $productId = Mage::getModel('catalog/product')->getIdBySku($return['Sku']);
            if(!$productId) {
                $productId = $item->getProductId();
            }
            $product = Mage::getModel('catalog/product')->load($productId);

            if ($product) {
                $product->getAttributes();
                $i=1;
                foreach ($attributeCodes as $attributeCode) {
                    $customName = $i == 1 ? 'custom' : 'custom' . $i;
                    if ($attributeCode == 'category_ids') {
                        $firstCategory = $product->getCategoryCollection()->addAttributeToSelect('name')->getFirstItem();
                        $return[$customName] = $firstCategory->getName();
                    } elseif ($attributeCode) {
                        $attribute = $product->getResource()->getAttribute($attributeCode);
                        if ($attribute) {
                            if ($attribute->getBackendModel() == 'weee/attribute_backend_weee_tax') {
                                $itemWeeeTaxApplied = $item->getWeeeTaxApplied();
                                if ($itemWeeeTaxApplied) {
                                    $html = '';
                                    $itemWeeeTaxApplied = unserialize($itemWeeeTaxApplied);
                                    foreach ($itemWeeeTaxApplied as $appliedWeee) {
                                        if ($attribute->getFrontendLabel() == $appliedWeee['title']
                                            && $appliedWeee['base_amount']
                                        ) {
                                            $html .= $helper->OutputPrice(
                                                $appliedWeee['amount'],
                                                $appliedWeee['base_amount'],
                                                $helper->getDisplayBoth()
                                            );
                                        }
                                    }
                                }
                                $return[$customName] = $html;
                            } else {
                                $attributeValue = $helper->fixEncoding($product->getAttributeText($attributeCode));
                                if (is_array($attributeValue) && !empty($attributeValue)) {
                                    $return[$customName] = implode('<br/>', $attributeValue);
                                } else {
                                    $return[$customName] = $attributeValue;
                                    if (!$return[$customName]) {
                                        $return[$customName] = $helper->fixEncoding($product->getDataUsingMethod($attributeCode));
                                    }
                                }
                            }
                        }
                    }
                    $i++;
                }
            }
        }

        /*
        //Uncomment this block: delete /* and * / and enter your attribute code below
        $attributeCode ='attribute_code_from_Magento_backend';
        $productAttribute = Mage::getModel('catalog/product')->load($item->getProductId())->getData($attributeCode);
        if(!empty($productAttribute)){
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
            $return['Name'] .= "<br/><br/>".$attribute->getFrontendLabel().": ".$productAttribute;
        }
         */
        return $return;
    }


    /**
     * load customer addresses in defined format
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $order
     * @param                                 $which
     *
     * @return mixed|string
     */
    public function PrepareCustomerAddress(Fooman_PdfCustomiser_Helper_Pdf $helper, $order, $which)
    {

        if (version_compare(Mage::getVersion(), '1.4.2.0') < 0) {
            $format = Mage::getStoreConfig('sales_pdf/all/alladdressformat', $helper->getStoreId());
        } else {
            $format = 'pdf';
        }
        Mage::getSingleton('customer/address_config')->setStore($helper->getStoreId());
        if ($which == 'billing') {
            $billingAddress = $order->getBillingAddress()->format($format);
            if ($order->getCustomerTaxvat()) {
                $billingAddress .= "<br/>" . Mage::helper('sales')->__('TAX/VAT Number') . ": "
                    . $order->getCustomerTaxvat();
            } elseif (!$order->getCustomerIsGuest()) {
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                if ($customer->getTaxvat()) {
                    $billingAddress .= "<br/>" . Mage::helper('sales')->__('TAX/VAT Number') . ": "
                        . $customer->getTaxvat();
                }
            }
            // show the email address underneath the billing address
            if (Mage::getStoreConfig('sales_pdf/all/alldisplayemail', $helper->getStoreId())) {
                $billingAddress .= "<br/>" . $order->getCustomerEmail();
            }
            $billingAddress = str_replace("|", "<br/>", $billingAddress);
            $billingAddress = preg_replace("/(<br\s*\/?>\s*)+/", "<br/>",$billingAddress);
            return $this->_fixAddressEncoding($billingAddress);
        } else {
            if (!$order->getIsVirtual()) {
                $shippingAddress = $order->getShippingAddress()->format($format);
            } else {
                $shippingAddress = '';
            }
            $shippingAddress = str_replace("|", "<br/>", $shippingAddress);
            $shippingAddress = preg_replace("/(<br\s*\/?>\s*)+/", "<br/>", $shippingAddress);
            return $this->_fixAddressEncoding($shippingAddress);
        }
    }

    /**
     *  output customer addresses
     *
     * @param \Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                  $order
     * @param                                  $addresses
     *
     * @return void
     */
    public function OutputCustomerAddresses(Fooman_PdfCustomiser_Helper_Pdf $helper, $order, $addresses)
    {
        $shippingAddress = $this->PrepareCustomerAddress($helper, $order, 'shipping');
        $billingAddress = $this->PrepareCustomerAddress($helper, $order, 'billing');
        $labelAddress = false;

        //which addresses are we supposed to show
        $addresses = explode(',',$addresses);
        foreach ($addresses as $which){
            switch ($which) {
            case 'both':
                //swap order for Packing Slips - shipping on the left
                if ($helper instanceof Fooman_PdfCustomiser_Helper_Pdf_Shipment) {
                    $this->SetX($helper->getPdfMargins('sides') + 5);
                    $this->Cell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'),
                        0,
                        Mage::helper('sales')->__('Ship to:'),
                        0, 0, 'L'
                    );
                    if (!$order->getIsVirtual()) {
                        $this->Cell(0, 0, Mage::helper('sales')->__('Sold to:'), 0, 1, 'L');
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->SetX($helper->getPdfMargins('sides') + 10);
                    $this->writeHTMLCell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'),
                        0, null, null,
                        $shippingAddress,
                        null, 0
                    );
                    if (!$order->getIsVirtual()) {
                        $this->writeHTMLCell(0, $this->getLastH(), null, null, $billingAddress, null, 1);
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->Ln(10);
                    break;
                } else {
                    $this->SetX($helper->getPdfMargins('sides') + 5);
                    $this->Cell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'),
                        0,
                        Mage::helper('sales')->__('Sold to:'),
                        0, 0, 'L'
                    );
                    if (!$order->getIsVirtual()) {
                        $this->Cell(0, 0, Mage::helper('sales')->__('Ship to:'), 0, 1, 'L');
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->SetX($helper->getPdfMargins('sides') + 10);
                    $this->writeHTMLCell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'),
                        0, null, null,
                        $billingAddress,
                        null, 0
                    );
                    if (!$order->getIsVirtual()) {
                        $this->writeHTMLCell(0, $this->getLastH(), null, null, $shippingAddress, null, 1);
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->Ln(10);
                    break;
                }
            case 'billing':
                $this->SetX($helper->getPdfMargins('sides') + 5);
                $this->writeHTMLCell(0, 0, null, null, $billingAddress, null, 1);
                $this->Ln(10);
                break;
            case 'shipping':
                $this->SetX($helper->getPdfMargins('sides') + 5);
                if (!$order->getIsVirtual()) {
                    $this->writeHTMLCell(0, 0, null, null, $shippingAddress, null, 1);
                }
                $this->Ln(10);
                break;
            case 'singlebilling':
                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $this->SetXY(-180, -67);
                $this->writeHTMLCell(75, 0, null, null, $billingAddress, null, 0);
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;
            case 'singleshipping':
                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $this->SetXY(-180, -67);
                if (!$order->getIsVirtual()) {
                    $this->writeHTMLCell(75, $this->getLastH(), null, null, $shippingAddress, null, 1);
                }
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;
            case 'double':
                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $this->SetXY(-180, -67);
                $this->writeHTMLCell(75, 0, null, null, $billingAddress, null, 0);
                $this->SetXY(-95, -67);
                if (!$order->getIsVirtual()) {
                    $this->writeHTMLCell(75, $this->getLastH(), null, null, $shippingAddress, null, 1);
                }
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;
            case 'label1-billing':
                $labelAddress = $order->getBillingAddress();
                //no break intentional
            case 'label1-shipping':
                if (!$labelAddress) {
                    $labelAddress = $order->getShippingAddress();
                }
                $formatType = Mage::getSingleton('customer/address_config')->getFormatByCode('label');
                $formattedAddress = $formatType->getRenderer()->render(
                    $labelAddress,
                    null,
                    'fooman/pdfcustomiser/label-left.phtml',
                    $helper
                );

                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $this->SetXY(20, -77);
                $this->writeHTMLCell(85, 0, null, null, $formattedAddress, 0, 0);
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;

            case 'label2-billing':
                $labelAddress = $order->getBillingAddress();
                //no break intentional
            case 'label2-shipping':
                if (!$labelAddress) {
                    $labelAddress = $order->getShippingAddress();
                }
                $formatType = Mage::getSingleton('customer/address_config')->getFormatByCode('label');
                $formattedAddress = $formatType->getRenderer()->render(
                    $labelAddress,
                    null,
                    'fooman/pdfcustomiser/label-right.phtml',
                    $helper
                );
                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $this->SetXY(-105, -77);
                $this->writeHTMLCell(85, 0, null, null, $formattedAddress, 0, 0);
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;
            case 'doublereturn':
                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $this->MultiCell(
                    75, 47,
                    Mage::helper('pdfcustomiser')->__('Return Address') . ":\n\n" . $helper->getPdfOwnerAddresss(),
                    0, 'L', 0, 0, 30, 230
                );
                if (!$order->getIsVirtual()) {
                    $this->writeHTMLCell(75, 47, 115, 230, $shippingAddress, null, 0);
                }
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;
            case 'doubleimage':
                $this->SetAutoPageBreak(false);
                //$this->setPrintFooter(false);
                $image = Mage::getBaseDir('media') . DS . 'pdf-printouts' . DS .'print_label.gif';
                $this->Image(
                    $image,
                    15, 225, 50, 25,
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = true
                );
                $this->Image(
                    $image,
                    110, 225, 50, 25,
                    $type = '',
                    $link = '',
                    $align = '',
                    $resize = false,
                    $dpi = 300,
                    $palign = '',
                    $ismask = false,
                    $imgmask = false,
                    $border = 0,
                    $fitbox = true
                );
                $content = strtoupper($shippingAddress).'<br/>';
                if ($order->getDeliveryNotes()) {
                    $content .= '<font size="8">Delivery notes:' . $order->getDeliveryNotes() . "</font>";
                }

                $this->writeHTMLCell(75, 47, 15, 245, $content, null, 0);
                $this->writeHTMLCell(75, 47, 110, 245, $content, null, 0);
                $this->writeHTMLCell(
                    75, 47, 15, 280,
                    '<font size="6">'.Mage::helper('pdfcustomiser')->__('RETURN ADDRESS') . ' - '
                        . $helper->getPdfOwnerAddresss().'</font>',
                    null, 0
                );
                $this->writeHTMLCell(
                    75, 47, 110, 280,
                    '<font size="6">'.Mage::helper('pdfcustomiser')->__('RETURN ADDRESS') . ' - '
                        . $helper->getPdfOwnerAddresss().'</font>',
                    null, 0
                );
                $this->SetAutoPageBreak(true, $helper->getBottomPageBreak());
                break;
            default:
                $this->SetX($helper->getPdfMargins('sides') + 5);
                $this->writeHTMLCell(0, 0, null, null, $billingAddress, null, 1);
                $this->Ln(10);
            }
        }
    }

    /**
     * Prepare the payment info as html
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $order
     * @param                                 $printItem
     *
     * @return mixed
     * @throws Exception
     */
    public function PreparePayment(Fooman_PdfCustomiser_Helper_Pdf $helper, $order, $printItem)
    {
        try {
            $order->getPayment()->getMethodInstance()->setStore($helper->getStoreId());
        } catch (Exception $e) {
            //in case the payment method has been deleted.
            return $order->getPayment()->getMethod();
        }

        //try if template exists in admin for pdf
        try {
            $theme = (string)Mage::getConfig()->getNode('stores/admin/design/theme/default');
            if (empty($theme)) {
                $theme = 'default';
            }
            Mage::getDesign()->setPackageName('default');
            Mage::getDesign()->setTheme($theme);

            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true)
                ->setArea(Mage_Core_Model_App_Area::AREA_ADMINHTML)
                ->setTheme($theme)
                ->setPackageName('default');
            $paymentBlock->getMethod()->setStore($helper->getStoreId());;
            $paymentInfo = $paymentBlock->toPdf();

            if (!$paymentInfo) {
                throw new Exception('empty payment method - try toHtml method');
            }
            //unfortunately not all payment methods supply a file/method, fall back on standard html output
        } catch (Exception $e) {
            Mage::getDesign()->setPackageName('');
            Mage::getDesign()->setTheme('');
            $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true)
                ->toHtml();
        }
        Mage::getDesign()->setPackageName('');
        Mage::getDesign()->setTheme('');

        $paymentInfo = str_replace("{{pdf_row_separator}}", "<br/>", $paymentInfo);
        return $paymentInfo;
    }

    /**
     * Prepare shipping info as html
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $order
     * @param                                 $printItem
     *
     * @return string
     */

    public function PrepareShipping(Fooman_PdfCustomiser_Helper_Pdf $helper, $order, $printItem)
    {
        if (!$order->getIsVirtual()) {
            //display depending on if Total Weight should be displayed or not
            $totalWeight = false;
            if ($helper->displayWeight()) {
                //calculate weight
                $totalWeight = 0;
                $dedup = array();
                foreach ($printItem->getAllItems() as $item) {
                    $key = $item->getSku();
                    if(!isset($dedup[$key]) && !$item->getParentItemId()){
                        if ($printItem instanceof Mage_Sales_Model_Order) {
                            $totalWeight += $item->getRowWeight();
                        } elseif($printItem instanceof Mage_Sales_Model_Order_Shipment) {
                            $totalWeight += $item->getQty() * $item->getWeight();
                        } else {
                            $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                            $totalWeight += $item->getQty() * $orderItem->getWeight();
                        }
                        $dedup[$key] = true;
                    }
                }
            }

            $block = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block', 'pdfcustomiser.shipping');
            $html = $block->setPdfHelper($helper)
                ->setTracks($order->getTracksCollection())
                ->setShippingDescription($helper->fixEncoding($order->getShippingDescription()))
                ->setTotalWeight($totalWeight)
                ->setTemplate('fooman/pdfcustomiser/shipping.phtml')
                ->toHtml();
            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            $processor->setVariables(
                array(
                     'order'        => $order,
                     'sales_object' => $helper->getSalesObject(),
                )
            );
            $html = $processor->filter($html);
            return $html;

        } else {
            return '';
        }
    }


    /**
     * Output shipping and payment info to pdf
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $order
     * @param                                 $printItem
     */
    public function OutputPaymentAndShipping(Fooman_PdfCustomiser_Helper_Pdf $helper, $order, $printItem)
    {

        $paymentInfo = $this->PreparePayment($helper, $order, $printItem);
        $shippingInfo = $this->PrepareShipping($helper, $order, $printItem);

        $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
        $this->Cell(
            0.5 * ($this->getPageWidth() - 2 * $helper->getPdfMargins('sides')),
            0,
            Mage::helper('sales')->__('Payment Method'),
            0,
            0,
            'L'
        );
        if (!$order->getIsVirtual()) {
            $this->Cell(0, 0, Mage::helper('sales')->__('Shipping Method'), 0, 1, 'L');
        } else {
            $this->Cell(0, 0, '', 0, 1, 'L');
        }

        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
        $this->writeHTMLCell(
            0.5 * ($this->getPageWidth() - 2 * $helper->getPdfMargins('sides')),
            0,
            null,
            null,
            $paymentInfo,
            0,
            0
        );
        $this->MultiCell(0, $this->getLastH(), $shippingInfo, 0, 'L', 0, 1);
        $this->Cell(0, 0, '', 0, 1, 'L');
    }

    /**
     * return Gift Message as Array for order item
     *
     * @param $item
     *
     * @return array
     */
    public function getGiftMessage($item)
    {
        $returnArray = array();
        $returnArray['title'] = '';
        $returnArray['from'] = '';
        $returnArray['to'] = '';
        $returnArray['message'] = '';
        if ($item->getGiftMessageId()) {
            $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($item->getGiftMessageId());
            if ($giftMessage) {
                $returnArray['from'] = htmlspecialchars($giftMessage->getSender());
                $returnArray['to'] = htmlspecialchars($giftMessage->getRecipient());
                $returnArray['message'] = htmlspecialchars($giftMessage->getMessage());
            }
        }
        return $returnArray;
    }

    /**
     * override parent function to change default style
     *
     * @param        $code
     * @param        $type
     * @param string $x
     * @param string $y
     * @param string $w
     * @param string $h
     * @param float  $xres
     * @param string $style
     * @param string $align
     */
    public function write1DBarcode(
        $code, $type, $x = '', $y = '', $w = '', $h = '', $xres = 0.4, $userStyle = array(), $align = 'T'
    )
    {
        $this->SetX($this->GetX()+4);
        $defaultStyle = array(
            'position'    => 'S',
            'border'      => false,
            'padding'     => 1,
            'fgcolor'     => array(0, 0, 0),
            'bgcolor'     => false,
            'text'        => true,
            'font'        => 'helvetica',
            'fontsize'    => 8,
            'stretchtext' => 4
        );
        $style = $userStyle + $defaultStyle;
        parent::write1DBarcode($code, $type, $x, $y, $w, $h, $xres, $style, $align);
    }

    /**
     * see above - used for outputting barcodes of bundled products - smaller and no sku as text
     */
    public function write1DBarcode2(
        $code, $type, $x = '', $y = '', $w = '', $h = '', $xres = 0.4, $userStyle = array(), $align = 'T'
    )
    {
        $this->SetX($this->GetX()+4);
        $defaultStyle = array(
            'position'    => 'S',
            'border'      => false,
            'padding'     => 1,
            'fgcolor'     => array(0, 0, 0),
            'bgcolor'     => false,
            'text'        => false,
            'font'        => 'helvetica',
            'fontsize'    => 8,
            'stretchtext' => 4
        );
        $style = $userStyle + $defaultStyle;
        parent::write1DBarcode($code, $type, $x, $y, $w, $h, $xres, $style, $align);
    }

    /**
     * sorting helper function for item listings
     *
     * @param $a
     * @param $b
     *
     * @return bool
     */
    private function cmp($a, $b)
    {
        $productDetailsArray = array('custom','custom2','custom3','custom4','custom5','Sku','Name');
        if (in_array($this->_sortBy, $productDetailsArray)) {
            return $a['productDetails'][$this->_sortBy] > $b['productDetails'][$this->_sortBy];
        }
        //return strcmp($a[$this->_sortBy], $b[$this->_sortBy]);
        return $a[$this->_sortBy] > $b[$this->_sortBy];
    }

    /**
     * load product image
     * consider configurable relations, ie try subitem first
     * and if not suitable use the parent item's image
     *
     * @param      $productId
     * @param null $sku
     * @param null $parentProductId
     *
     * @return string
     */
    protected function prepareProductImage($productId, $sku = null, $parentProductId = null)
    {
        $productImage = false;
        //try via sku first (configurables)
        if ($sku) {
            $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('sku', $sku)->setPageSize(1);
            $product = $collection->getFirstItem();
        } else {
            $product = false;
        }

        if ($product instanceof Mage_Catalog_Model_Product) {
            if (!$product->getId()
                || (!$product->getImage() && !$product->getSmallImage())
                || $product->getImage() == 'no_selection'
            ) {
                $product = Mage::getModel('catalog/product')->load($productId);
            }
        } else {
            $product = Mage::getModel('catalog/product')->load($productId);
        }

        if ($product->getId()) {
            $productImage = $product->getImage() ? $product->getImage() : $product->getSmallImage();
        }

        if ((!$productImage || $productImage == "no_selection") && $parentProductId) {
            $product = Mage::getModel('catalog/product')->load($parentProductId);
            if ($product->getId()) {
                $productImage = $product->getImage();
            }
        }

        //when using OrganicInternet_SimpleConfigurableProducts simple and configurable products
        //become detached - check if there is exactly one parent that has an image
        if (Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')
            && ($productImage || $productImage == "no_selection")
        ) {
            $ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
            if(count($ids) == 1) {
                $product = Mage::getModel('catalog/product')->load(current($ids));
                if ($product->getId()) {
                    $productImage = $product->getImage();
                }
            }
        }

        $imagePath = DS .'catalog' . DS . 'product' . $productImage;
        if ($productImage
            && $productImage != "no_selection"
            && file_exists(Mage::getBaseDir('media').$imagePath)
        ) {
            $imageAttr = $product->getImage() ? 'image' : 'small_image';
            $imageHelper = Mage::helper('catalog/image')->init($product, $imageAttr)->resize(500);
            return (string)$imageHelper;
        }

        return false;
    }

    /**
     * Get the image gallery associated with the product
     *
     * @param $product
     *
     * @return array
     */
    protected function getGalleryImages($product)
    {
        $returnArray = array();
        if ($product->getMediaGalleryImages()) {
            foreach ($product->getMediaGalleryImages() as $galleryImage) {
                 $returnArray[] = $galleryImage['path'];
            }
        }
        return $returnArray;
    }

    /**
     * if tax rates are available on item level split amounts and add to cumulative Tax Totals
     * else
     * add straight to cumulative Tax Totals
     *
     * @param      $item
     * @param      $taxPercent
     * @param bool $parentTaxRate
     */
    protected function trackItemTax($item, $taxPercent, $parentTaxRate = false)
    {
        $taxHelper = Mage::helper('tax');
        if (method_exists($taxHelper, 'getCalculatedTaxes')) {
            if ($item instanceof Mage_Sales_Model_Order_Item) {
                $itemId = $item->getItemId();
            } else {
                $itemId = $item->getOrderItemId();
            }

            //newer versions of Magento have a break down of individual tax percentages applied per item level
            $taxCollection = Mage::getResourceModel('tax/sales_order_tax_item')->getTaxItemsByItemId(
                $itemId
            );
            $nrTaxes = sizeof($taxCollection);
            $i = 1;
            $remainingItemTaxes = $item->getBaseTaxAmount();
            if ($nrTaxes) {
                foreach ($taxCollection as $collectedTaxes) {
                    $taxPercent = $collectedTaxes['tax_percent'];
                    if ($nrTaxes == $i) {
                        //if only one tax rate has been applied we can take the value as is
                        //or if last tax rate take remainder of applied tax amount
                        $this->addAmountsToTaxTotals(
                            $taxPercent,
                            $remainingItemTaxes,
                            $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
                                + $item->getBaseHiddenTaxAmount()
                        );
                    } else {
                        //the actual amount applied per tax percentage is not available
                        //recalculate here
                        $recalculatedTax = ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()
                            + $item->getBaseHiddenTaxAmount())
                            * ($taxPercent / 100);
                        $recalculatedTax = Mage::app()->getStore()->roundPrice($recalculatedTax);
                        $this->addAmountsToTaxTotals(
                            $taxPercent,
                            $recalculatedTax,
                            $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
                                + $item->getBaseHiddenTaxAmount()
                        );
                        $remainingItemTaxes -= $recalculatedTax;
                    }
                    $i++;
                }
            } else {
                $this->addAmountsToTaxTotals(
                    $taxPercent,
                    0,
                    $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
                        + $item->getBaseHiddenTaxAmount()
                );
            }
        } else {
            //bundle subitems could have tax but a zero tax rate. Use the parent item tax rate instead
            if ($taxPercent == 0 && $item->getBaseTaxAmount() != 0 && $parentTaxRate) {
                $taxPercent = $parentTaxRate;
            }
            $this->addAmountsToTaxTotals(
                $taxPercent,
                $item->getBaseTaxAmount(),
                $item->getBaseRowTotal() - $item->getBaseDiscountAmount() + $item->getBaseHiddenTaxAmount()
            );
        }
        $this->_hiddenTaxAmount += $item->getHiddenTaxAmount();
        $this->_baseHiddenTaxAmount += $item->getBaseHiddenTaxAmount();
    }

    /**
     * Check if tax rate is similar enough and add it to the cumulative tox totals
     *
     * @param      $netTaxAmount
     * @param      $tax
     * @param      $rate
     * @param      $recordResult
     * @param      $altRate
     *
     * @return bool
     */
    protected function checkTaxTotal($netTaxAmount, $tax, $rate, &$recordResult, $altRate = false)
    {
        if (Mage::helper('pdfcustomiser/tax')->isTaxRateSimilar($netTaxAmount, $tax, $rate)) {
            $recordResult = sprintf("%01.4f", $rate);
            $this->addAmountsToTaxTotals(
                sprintf("%01.4f", $rate),
                $tax,
                $netTaxAmount
            );
            return true;
        } elseif ($altRate && Mage::helper('pdfcustomiser/tax')->isTaxRateSimilar($netTaxAmount, $tax, $altRate)) {
            $recordResult = sprintf("%01.4f", $altRate);
            $this->addAmountsToTaxTotals(
                sprintf("%01.4f", $altRate),
                $tax,
                $netTaxAmount
            );
            return true;
        } else {
            return false;
        }
    }

    protected function _fixAddressEncoding($string)
    {
        return Mage::helper('core')->escapeHtml($string, array('b', 'br'));
    }

    /**
     * Overriden to allow unicode in filenames
     * @see http://sourceforge.net/p/tcpdf/feature-requests/184/
     *
     * Send the document to a given destination: string, local file or browser.
     * In the last case, the plug-in may be used (if present) or a download ("Save as" dialog box) may be forced.<br />
     * The method first calls Close() if necessary to terminate the document.
     * @param $name (string) The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
     * @param $dest (string) Destination where to send the document. It can take one of the following values:<ul><li>I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.</li><li>D: send to the browser and force a file download with the name given by name.</li><li>F: save to a local server file with the name given by name.</li><li>S: return the document as a string (name is ignored).</li><li>FI: equivalent to F + I option</li><li>FD: equivalent to F + D option</li><li>E: return the document as base64 mime multi-part email attachment (RFC 2045)</li></ul>
     * @public
     * @since 1.0
     * @see Close()
     */
    public function Output($name='doc.pdf', $dest='I') {
        //Output PDF to some destination
        //Finish document if necessary
        if ($this->state < 3) {
            $this->Close();
        }
        //Normalize parameters
        if (is_bool($dest)) {
            $dest = $dest ? 'D' : 'F';
        }
        $dest = strtoupper($dest);
        if ($dest{0} != 'F') {
            $name = preg_replace('/[\s]+/', '_', $name);
            $name = preg_replace('/[^\p{L}\p{N}_\.-]/u', '', $name);
        }
        if ($this->sign) {
            // *** apply digital signature to the document ***
            // get the document content
            $pdfdoc = $this->getBuffer();
            // remove last newline
            $pdfdoc = substr($pdfdoc, 0, -1);
            // Remove the original buffer
            if (isset($this->diskcache) AND $this->diskcache) {
                // remove buffer file from cache
                unlink($this->buffer);
            }
            unset($this->buffer);
            // remove filler space
            $byterange_string_len = strlen(TCPDF_STATIC::$byterange_string);
            // define the ByteRange
            $byte_range = array();
            $byte_range[0] = 0;
            $byte_range[1] = strpos($pdfdoc, TCPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
            $byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
            $byte_range[3] = strlen($pdfdoc) - $byte_range[2];
            $pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
            // replace the ByteRange
            $byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
            $byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
            $pdfdoc = str_replace(TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
            // write the document to a temporary folder
            $tempdoc = TCPDF_STATIC::getObjFilename('doc');
            $f = fopen($tempdoc, 'wb');
            if (!$f) {
                $this->Error('Unable to create temporary file: '.$tempdoc);
            }
            $pdfdoc_length = strlen($pdfdoc);
            fwrite($f, $pdfdoc, $pdfdoc_length);
            fclose($f);
            // get digital signature via openssl library
            $tempsign = TCPDF_STATIC::getObjFilename('sig');
            if (empty($this->signature_data['extracerts'])) {
                openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
            } else {
                openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
            }
            unlink($tempdoc);
            // read signature
            $signature = file_get_contents($tempsign);
            unlink($tempsign);
            // extract signature
            $signature = substr($signature, $pdfdoc_length);
            $signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
            $tmparr = explode("\n\n", $signature);
            $signature = $tmparr[1];
            unset($tmparr);
            // decode signature
            $signature = base64_decode(trim($signature));
            // convert signature to hex
            $signature = current(unpack('H*', $signature));
            $signature = str_pad($signature, $this->signature_max_length, '0');
            // disable disk caching
            $this->diskcache = false;
            // Add signature to the document
            $this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
            $this->bufferlen = strlen($this->buffer);
        }
        switch($dest) {
            case 'I': {
                // Send PDF to the standard output
                if (ob_get_contents()) {
                    $this->Error('Some data has already been output, can\'t send PDF file');
                }
                if (php_sapi_name() != 'cli') {
                    // send output to a browser
                    header('Content-Type: application/pdf');
                    if (headers_sent()) {
                        $this->Error('Some data has already been output to browser, can\'t send PDF file');
                    }
                    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                    //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Content-Disposition: inline; filename="'.basename($name).'"');
                    TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
                } else {
                    echo $this->getBuffer();
                }
                break;
            }
            case 'D': {
                // download PDF as file
                if (ob_get_contents()) {
                    $this->Error('Some data has already been output, can\'t send PDF file');
                }
                header('Content-Description: File Transfer');
                if (headers_sent()) {
                    $this->Error('Some data has already been output to browser, can\'t send PDF file');
                }
                header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                header('Pragma: public');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                // force download dialog
                if (strpos(php_sapi_name(), 'cgi') === false) {
                    header('Content-Type: application/force-download');
                    header('Content-Type: application/octet-stream', false);
                    header('Content-Type: application/download', false);
                    header('Content-Type: application/pdf', false);
                } else {
                    header('Content-Type: application/pdf');
                }
                // use the Content-Disposition header to supply a recommended filename
                header('Content-Disposition: attachment; filename="'.basename($name).'"');
                header('Content-Transfer-Encoding: binary');
                TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
                break;
            }
            case 'F':
            case 'FI':
            case 'FD': {
                // save PDF to a local file
                if ($this->diskcache) {
                    copy($this->buffer, $name);
                } else {
                    $f = fopen($name, 'wb');
                    if (!$f) {
                        $this->Error('Unable to create output file: '.$name);
                    }
                    fwrite($f, $this->getBuffer(), $this->bufferlen);
                    fclose($f);
                }
                if ($dest == 'FI') {
                    // send headers to browser
                    header('Content-Type: application/pdf');
                    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                    //header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Content-Disposition: inline; filename="'.basename($name).'"');
                    TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
                } elseif ($dest == 'FD') {
                    // send headers to browser
                    if (ob_get_contents()) {
                        $this->Error('Some data has already been output, can\'t send PDF file');
                    }
                    header('Content-Description: File Transfer');
                    if (headers_sent()) {
                        $this->Error('Some data has already been output to browser, can\'t send PDF file');
                    }
                    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    // force download dialog
                    if (strpos(php_sapi_name(), 'cgi') === false) {
                        header('Content-Type: application/force-download');
                        header('Content-Type: application/octet-stream', false);
                        header('Content-Type: application/download', false);
                        header('Content-Type: application/pdf', false);
                    } else {
                        header('Content-Type: application/pdf');
                    }
                    // use the Content-Disposition header to supply a recommended filename
                    header('Content-Disposition: attachment; filename="'.basename($name).'"');
                    header('Content-Transfer-Encoding: binary');
                    TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
                }
                break;
            }
            case 'E': {
                // return PDF as base64 mime multi-part email attachment (RFC 2045)
                $retval = 'Content-Type: application/pdf;'."\r\n";
                $retval .= ' name="'.$name.'"'."\r\n";
                $retval .= 'Content-Transfer-Encoding: base64'."\r\n";
                $retval .= 'Content-Disposition: attachment;'."\r\n";
                $retval .= ' filename="'.$name.'"'."\r\n\r\n";
                $retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
                return $retval;
            }
            case 'S': {
                // returns PDF as a string
                return $this->getBuffer();
            }
            default: {
            $this->Error('Incorrect output destination: '.$dest);
            }
        }
        return '';
    }
}
