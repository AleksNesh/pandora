<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_OrderStatuses
{
    public function toOptionArray($isMultiSelect = false)
    {
        $orderStatusCollection = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $status = array(
           array('value' => "", 'label' => '--Please Select--')
        );

        foreach($orderStatusCollection as $orderStatus) {
            $status[] = array (
                'value' => $orderStatus['status'], 'label' => $orderStatus['label']
            );
        }
        return $status;
    }
}