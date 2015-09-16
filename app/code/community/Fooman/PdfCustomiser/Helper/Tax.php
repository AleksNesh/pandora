<?php
class Fooman_PdfCustomiser_Helper_Tax extends Mage_Tax_Helper_Data
{

    const TAX_SIMILARITY_THRESHOLD = 0.07;

    /**
     * the original Magento implementation provides wrong values for invoices
     * the regenerate tax subtotals should be executed
     *
     * @see Mage_Tax_Helper_Data::getCalculatedTaxes
     *
     * @param      $source
     * @param bool $includeShipping
     *
     * @return array
     */
    public function getCalculatedTaxFixed($source, $includeShipping = true)
    {
        $taxClassAmount = array();
        $processedShipping = false;

        $shippingAmount = $source->getShippingAmount();
        $baseShippingAmount = $source->getBaseShippingAmount();
        $shippingTaxAmount = $source->getShippingTaxAmount();
        $baseShippingTaxAmount = $source->getBaseShippingTaxAmount();

        foreach ($source->getItemsCollection() as $item) {
            $taxCollection = Mage::getResourceModel('tax/sales_order_tax_item')
                ->getTaxItemsByItemId(
                    $item->getOrderItemId() ? $item->getOrderItemId() : $item->getItemId()
                );

            foreach ($taxCollection as $tax) {
                $taxClassId = $tax['tax_id'];
                $percent = $tax['tax_percent'];

                $price = $item->getRowTotal();
                $basePrice = $item->getBaseRowTotal();
                if ($this->applyTaxAfterDiscount($item->getStoreId())) {
                    $price = $price - $item->getDiscountAmount() + $item->getHiddenTaxAmount();
                    $basePrice = $basePrice - $item->getBaseDiscountAmount() + $item->getBaseHiddenTaxAmount();
                }
                $tax_amount =  $this->taxItemRound($price * $percent / 100, $item->getStoreId());
                $base_tax_amount =  $this->taxItemRound($basePrice * $percent / 100, $item->getStoreId());

                if ($includeShipping && $shippingTaxAmount && !$processedShipping) {
                    if ($this->isTaxRateSimilar($shippingAmount, $shippingTaxAmount, $percent)) {
                        $tax_amount = $this->taxItemRound($tax_amount
                            + $shippingAmount * $percent / 100, $item->getStoreId());
                        $base_tax_amount = $this->taxItemRound($base_tax_amount
                            + $baseShippingAmount * $percent / 100, $item->getStoreId());
                        //bug fix for ShippingIncl != Shipping + Shipping Tax
                        if ($source->getBaseShippingInclTax()
                            && (abs($source->getBaseShippingInclTax() - $shippingAmount - $shippingTaxAmount) >= 0.005)
                        ) {
                            $tax_amount -= $this->taxItemRound(
                                $source->getShippingInclTax() - $shippingAmount - $shippingTaxAmount,
                                $item->getStoreId()
                            );
                            $base_tax_amount -= $this->taxItemRound(
                                $source->getBaseShippingInclTax() - $baseShippingAmount - $baseShippingTaxAmount,
                                $item->getStoreId()
                            );
                        }
                        $processedShipping = true;
                    }
                }

                if (isset($taxClassAmount[$taxClassId])) {
                    $taxClassAmount[$taxClassId]['tax_amount'] += $tax_amount;
                    $taxClassAmount[$taxClassId]['base_tax_amount'] += $base_tax_amount;
                } else {
                    $taxClassAmount[$taxClassId]['tax_amount'] = $tax_amount;
                    $taxClassAmount[$taxClassId]['base_tax_amount'] = $base_tax_amount;
                    $taxClassAmount[$taxClassId]['title'] = $tax['title'];
                    $taxClassAmount[$taxClassId]['percent'] = $tax['percent'];
                }
            }
        }

        if ($includeShipping && !$processedShipping) {
            $taxHelper =  Mage::helper('tax');
            if ($includeShipping && method_exists($taxHelper, 'getShippingTax')) {
                $shippingTaxes = $taxHelper->getShippingTax($source);
                if ($shippingTaxes) {
                    foreach ($shippingTaxes as $shippingTax) {
                        $id = $shippingTax['title'];
                        $taxClassAmount[$id]['tax_amount'] = $shippingTax['tax_amount'];
                        $taxClassAmount[$id]['base_tax_amount'] = $shippingTax['base_tax_amount'];
                        $taxClassAmount[$id]['title'] = $shippingTax['title'];
                        $taxClassAmount[$id]['percent'] = $shippingTax['percent'];
                    }
                }
            }
        }
        foreach ($taxClassAmount as $key => $tax) {
            if ($tax['tax_amount'] == 0 && $tax['base_tax_amount'] == 0) {
                unset($taxClassAmount[$key]);
            }
        }

        $taxClassAmount = array_values($taxClassAmount);


        return $taxClassAmount;
    }

    /**
     * Determine if the tax rate is within our proximity threshold
     *
     * @param $netTaxAmount
     * @param $tax
     * @param $rate
     *
     * @return bool
     */
    public function isTaxRateSimilar($netTaxAmount, $tax, $rate)
    {
        if ($tax == 0) {
            return false;
        }
        $diff = ($netTaxAmount * ($rate / 100)) / $tax;

        return (
            $diff < 1 + self::TAX_SIMILARITY_THRESHOLD
            && $diff > 1 - self::TAX_SIMILARITY_THRESHOLD
        );
    }

    public function taxItemRound($taxAmount, $storeId)
    {
        //Note: This setting is not saved with order history - assume constant
        $taxAlgorithm = Mage::getSingleton('tax/config')->getAlgorithm($storeId);
        if ($taxAlgorithm == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
            return $taxAmount;
        } else {
            return Mage::app()->getStore()->roundPrice($taxAmount);
        }
    }
}