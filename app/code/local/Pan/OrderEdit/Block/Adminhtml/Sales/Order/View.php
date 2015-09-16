<?php
/**
 * Extend/Override TinyBrick_OrderEdit module
 *
 * @category    Pan
 * @package     Pan_OrderEdit
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_OrderEdit_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function __construct()
    {
        $order = $this->getOrder();

        // removes the 'Edit' button by disallowing
        // Editing of an order to avoid confusion
        if ($this->_isAllowedAction('edit') && $order->canEdit()) {
            $order->setActionFlag('edit', false);
        }

        parent::__construct();
    }
}
