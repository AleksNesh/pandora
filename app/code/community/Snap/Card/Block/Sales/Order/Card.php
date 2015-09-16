<?php

class Snap_Card_Block_Sales_Order_Card extends Mage_Core_Block_Template
{
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Retreive gift cards applied to current order
     *
     * @return array
     */
    public function getSnapCards()
    {
        $result = array();
        $source = $this->getSource();
        if (!($source instanceof Mage_Sales_Model_Order)) {
            return $result;
        }
        $cards = Mage::helper('snap_card')->getCards($this->getOrder());
        foreach ($cards as $card) {
            $obj = new Varien_Object();
            $obj->setBaseAmount($card['ba'])
                ->setAmount($card['a'])
                ->setCode($card['c']);

            $result[] = $obj;
        }
        return $result;
    }

    /**
     * Initialize giftcard order total
     *
     * @return $this
     */
    public function initTotals()
    {
        $total = new Varien_Object(array(
            'code'      => $this->getNameInLayout(),
            'block_name'=> $this->getNameInLayout(),
            'area'      => $this->getArea()
        ));
        $this->getParentBlock()->addTotalBefore($total, array('customerbalance', 'grand_total'));
        return $this;
    }

    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
