<?php

class Magestore_Giftwrap_Block_Sales_Order_Creditmemo_Total_Giftwrap extends Mage_Core_Block_Template {

    public function initTotals() {

        $totalsBlock = $this->getParentBlock();
        $creditmemo = $totalsBlock->getCreditmemo();
        if ($creditmemo->getGiftwrapAmount() > 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => 'giftwrapamount',
                'label' => $this->__('Gift Wrap Amount'),
                'value' => $creditmemo->getGiftwrapAmount(),
                    )), 'subtotal');
        }
    }

}