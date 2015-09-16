<?php
/**
 *
 * @category    Brainvire
 * @package     Brainvire_OrderComment
 * @copyright   Copyright (c) 2011-2012 Brainvire Infotech Pvt. Ltd. <www.brainvire.com>
 */
class Brainvire_OrderComment_Model_Observer extends Varien_Object
{
    /**
     * Save comment from agreement form to order
     *
     * @param $observer
     */
    public function saveOrderComment($observer)
    {
        $orderComment = Mage::app()->getRequest()->getPost('ordercomment');
        if (is_array($orderComment) && isset($orderComment['comment'])) {
            $comment = trim($orderComment['comment']);

            if (!empty($comment)) {
                $order = $observer->getEvent()->getOrder(); 
                $order->setCustomerComment($comment);
                $order->setCustomerNoteNotify(true);
                $order->setCustomerNote($comment);
            }
        }
    }
}