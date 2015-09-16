<?php

/**
 * Simple module for custom override of Webtex_Giftcards module
 *
 * @category    Pan
 * @package     Pan_Giftcards
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_Giftcards_Block_Adminhtml_Card_Grid extends Webtex_Giftcards_Block_Adminhtml_Card_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumn('card_id', array(
            'header'    => Mage::helper('giftcards')->__('Card ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'card_id',
            'type'      => 'number',
        ));

        $this->addColumn('card_code', array(
            'header'    => Mage::helper('giftcards')->__('Card Code'),
            'align'     => 'left',
            'index'     => 'card_code',
        ));

        /**
         * BEGIN AAI HACK
         *
         * FIXES ISSUE WITH 'Invalid target currency.' exception raised
         * from Mage_Directory_Model_Currency::getAnyRate() method
         *
         * Changed 'currency' key to 'currency_code' and
         * pass the default currency (or 'USD' if you
         * really want to hard code it)
         *
         * @see  Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Price::getCondition
         *           specifically the line with: `$displayCurrency = $this->getColumn()->getCurrencyCode();`
         * @see  Mage_Directory_Model_Currency::getAnyRate()
         *
         */
        $this->addColumn('card_amount', array(
            'header'        => Mage::helper('giftcards')->__('Initial Value'),
            'type'          => 'price',
            // 'currency_code' => 'card_currency',
            'currency_code' => Mage::helper('giftcards')->getDefaultCurrency(),
            'index'         => 'card_amount',
        ));

        $this->addColumn('card_balance', array(
            'header'        => Mage::helper('giftcards')->__('Current Balance'),
            'type'          => 'price',
            // 'currency_code' => 'card_currency',
            'currency_code' => Mage::helper('giftcards')->getDefaultCurrency(),
            'index'         => 'card_balance',
        ));
        /**
         * END AAI HACK
         */

        $this->addColumn('mail_to', array(
            'header'    => Mage::helper('giftcards')->__('Recipient'),
            'align'     => 'left',
            'index'     => 'mail_to',
        ));
        $this->addColumn('mail_to_email', array(
            'header'    => Mage::helper('giftcards')->__('Recipient E-Mail'),
            'align'     => 'left',
            'index'     => 'mail_to_email',
        ));


        $this->addColumn('increment_id', array(
                'header'            =>  Mage::helper('customer')->__('Order'),
                'width'             => '100',
                'type'              => 'action',
                'actions'           => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('View'),
                        'url'       => array('base'=>'adminhtml/sales_order/view'),
                        'field'     => 'order_id',
                    )
                ),
                //'filter'            => false,
                //'sortable'          => false,
                'index'             => 'increment_id',
                'is_system'         => true,
                'getter'            => 'getOrderId',
                'frame_callback'    => array($this, 'getOrderLink'),
        ));

        $this->addColumn('created_time', array(
            'header'    => Mage::helper('giftcards')->__('Date Created'),
            'align'     => 'left',
            'index'     => 'created_time',
            'type'      => 'datetime',
            'width'     => '160px',
        ));

        $this->addColumn('card_type', array(
            'header'    => Mage::helper('giftcards')->__('Card Type'),
            'index'     => 'card_type',
            'type'      => 'options',
            'options'   => array(
                'print'     => Mage::helper('giftcards')->__('Print'),
                'email'     => Mage::helper('giftcards')->__('E-mail'),
                'offline'   => Mage::helper('giftcards')->__('Offline'),
            ),
        ));

        $this->addColumn('card_status', array(
            'header'    => Mage::helper('giftcards')->__('Status'),
            'index'     => 'card_status',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('giftcards')->__('Active'),
                '0' => Mage::helper('giftcards')->__('Inactive'),
                '2' => Mage::helper('giftcards')->__('Used'),
            ),
        ));

        $this->addColumn('card_actions', array(
            'header'    => Mage::helper('giftcards')->__('Action'),
            'width'     => 10,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'giftcards/adminhtml_card_grid_renderer_action',
        ));

        $this->sortColumnsByOrder();
        return $this;
        // return parent::_prepareColumns();
    }

    static function getOrderLink($renderedValue, $row, $column, $flag) {
        if ($row->getOrderId()) {
            $order = Mage::getModel('sales/order')->loadByAttribute('entity_id', $row->getOrderId());
            return str_replace('View', '#'.$order->getIncrementId(), $renderedValue);
        } else {
            return '';
        }
    }
}
