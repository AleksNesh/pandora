<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Model_Order_Address_Total_Tax extends TinyBrick_OrderEdit_Model_Order_Address_Total_Abstract
{
    /**
     * Used to see if the taxes have already been applied
     * @var string $_appliedTaxes
     */
    protected $_appliedTaxes = array();
    /**
     * Sets the tax code
     */
    public function __construct(){
        $this->setCode('tax');
    }
    /**
     * Collects the taxes
     * @param TinyBrick_OrderEdit_Model_Order_Address $address
     * @return TinyBrick_OrderEdit_Model_Order_Address_Total_Tax 
     */
    public function collect(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $store = $address->getOrder()->getStore();

        $address->getOrder()->setTaxAmount(0);
        $address->getOrder()->setBaseTaxAmount(0);
        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);
        $address->setAppliedTaxes(array());
		
		$order = $address->getOrder();
        $items = $order->getAllItems();
        if (!count($items)) {
            return $this;
        }
        $custTaxClassId = $address->getOrder()->getCustomerTaxClassId();
        

        $taxCalculationModel = Mage::getSingleton('tax/calculation');
        
        /**
         *  @var $taxCalculationModel Mage_Tax_Model_Calculation 
         */
        $request = $taxCalculationModel->getRateRequest($address->getOrder()->getShippingAddress(), $address->getOrder()->getBillingAddress(), $custTaxClassId, $store);

        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent
             */
            if ($item->getParentItemId()) {
                continue;
            }
            /**
             * We calculate parent tax amount as sum of children's tax amounts
             */

			if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $discountBefore = $item->getDiscountAmount();
                    $baseDiscountBefore = $item->getBaseDiscountAmount();

                    $rate = $taxCalculationModel->getRate($request->setProductClassId($child->getProduct()->getTaxClassId()));

                    $child->setTaxPercent($rate);
                    $child->calcTaxAmount();

                    if ($discountBefore != $item->getDiscountAmount()) {
                        $address->setDiscountAmount($address->getDiscountAmount()+($item->getDiscountAmount()-$discountBefore));
                        $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+($item->getBaseDiscountAmount()-$baseDiscountBefore));

                        $address->setGrandTotal($address->getGrandTotal() - ($item->getDiscountAmount()-$discountBefore));
                        $address->setBaseGrandTotal($address->getBaseGrandTotal() - ($item->getBaseDiscountAmount()-$baseDiscountBefore));
                    }

                    $this->_saveAppliedTaxes(
                       $address,
                       $taxCalculationModel->getAppliedRates($request),
                       $child->getTaxAmount(),
                       $child->getBaseTaxAmount(),
                       $rate
                    );
                }
                $address->setTaxAmount($address->getTaxAmount() + $item->getTaxAmount());
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $item->getBaseTaxAmount());
            } else {
                $discountBefore = $item->getDiscountAmount();
                $baseDiscountBefore = $item->getBaseDiscountAmount();
				
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
                $rate = $taxCalculationModel->getRate($request->setProductClassId($product->getTaxClassId()));

				if($item->getTaxExempt()) {
					$item->setTaxPercent(0);
				} else {
					$item->setTaxPercent($rate);
				}
                
                $item->calcTaxAmount();
                
                if ($discountBefore != $item->getDiscountAmount()) {
                    $address->setDiscountAmount($address->getDiscountAmount()+($item->getDiscountAmount()-$discountBefore));
                    $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+($item->getBaseDiscountAmount()-$baseDiscountBefore));

                    $address->setGrandTotal($address->getGrandTotal() - ($item->getDiscountAmount()-$discountBefore));
                    $address->setBaseGrandTotal($address->getBaseGrandTotal() - ($item->getBaseDiscountAmount()-$baseDiscountBefore));
                }
				
                $address->getOrder()->setTaxAmount($address->getOrder()->getTaxAmount() + $item->getTaxAmount());
                $address->getOrder()->setBaseTaxAmount($address->getOrder()->getBaseTaxAmount() + $item->getBaseTaxAmount());

                $applied = $taxCalculationModel->getAppliedRates($request);
                $this->_saveAppliedTaxes(
                   $address,
                   $applied,
                   $item->getTaxAmount(),
                   $item->getBaseTaxAmount(),
                   $rate
                );
            }
            
        }

        $shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);

        $shippingTax      = 0;
        $shippingBaseTax  = 0;

       if ($shippingTaxClass) {
            if ($rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass))) {
                if (!Mage::helper('tax')->shippingPriceIncludesTax()) {
                    $shippingTax    = $address->getShippingAmount() * $rate/100;
                    $shippingBaseTax= $address->getBaseShippingAmount() * $rate/100;
                } else {
                    $shippingTax    = $address->getShippingTaxAmount();
                    $shippingBaseTax= $address->getBaseShippingTaxAmount();
                }

                $shippingTax    = $store->roundPrice($shippingTax);
                $shippingBaseTax= $store->roundPrice($shippingBaseTax);

                $address->setTaxAmount($address->getOrder()->getTaxAmount() + $shippingTax);
                         
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $shippingBaseTax);

                $this->_saveAppliedTaxes(
                    $address,
                    $taxCalculationModel->getAppliedRates($request),
                    $shippingTax,
                    $shippingBaseTax,
                    $rate
                );
            }
        }

        if (!Mage::helper('tax')->shippingPriceIncludesTax()) {
            $address->setShippingTaxAmount($shippingTax);
            $address->setBaseShippingTaxAmount($shippingBaseTax);
        }
			
        $address->setGrandTotal($address->getGrandTotal() + $address->getOrder()->getTaxAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getOrder()->getBaseTaxAmount());
        
        return $this;
    }
    /**
     * Saves the new taxes to the order
     * @param TinyBrick_OrderEdit_Model_Order_Address $address
     * @param zrray $applied
     * @param int $amount Total amount owed
     * @param int $baseAmount Base amount owed
     * @param decimal $rate Tax rate
     */
    protected function _saveAppliedTaxes(TinyBrick_OrderEdit_Model_Order_Address $address, $applied, $amount, $baseAmount, $rate)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount/$rate*$row['percent'];
                $baseAppliedAmount = $baseAmount/$rate*$row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }


            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }
    /**
     * Fetch the new taxes for the address
     * @param TinyBrick_OrderEdit_Model_Order_Address $address
     * @return TinyBrick_OrderEdit_Model_Order_Address_Total_Tax 
     */
    public function fetch(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $applied = $address->getAppliedTaxes();
        $store = $address->getOrder()->getStore();
        $amount = $address->getTaxAmount();

        if (($amount!=0) || (Mage::helper('tax')->displayZeroTax($store))) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('sales')->__('Tax'),
                'full_info'=>$applied ? $applied : array(),
                'value'=>$amount
            ));
        }
        return $this;
    }
}