<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_ENABLED = 'mageworx_orderspro/general/enabled';

    const XML_ENABLE_INVOICE_ORDERS = 'mageworx_orderspro/general/enable_invoice_orders';
    const XML_SEND_INVOICE_EMAIL = 'mageworx_orderspro/general/send_invoice_email';
    const XML_ENABLE_SHIP_ORDERS = 'mageworx_orderspro/general/enable_ship_orders';
    const XML_SEND_SHIPMENT_EMAIL = 'mageworx_orderspro/general/send_shipment_email';

    const XML_ENABLE_ARCHIVE_ORDERS = 'mageworx_orderspro/general/enable_archive_orders';
    const XML_ENABLE_DELETE_ORDERS = 'mageworx_orderspro/general/enable_delete_orders';
    const XML_HIDE_DELETED_ORDERS_FOR_CUSTOMERS = 'mageworx_orderspro/general/hide_deleted_orders_for_customers';
    const XML_ENABLE_DELETE_ORDERS_COMPLETLY = 'mageworx_orderspro/general/enable_delete_orders_completely';

    const XML_GRID_COLUMNS = 'mageworx_orderspro/general/grid_columns';
    const XML_CUSTOMER_GRID_COLUMNS = 'mageworx_orderspro/general/customer_grid_columns';


    protected $_contentType = 'application/octet-stream';
    protected $_resourceFile = null;
    protected $_handle = null;


    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_ENABLED);
    }

    public function isShippingPriceEditEnabled()
    {
        return Mage::getStoreConfig('mageworx_orderspro/general/enable_shipping_price_edition');
    }

    public function isEnableInvoiceOrders()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_INVOICE_ORDERS);
    }

    public function isSendInvoiceEmail()
    {
        return Mage::getStoreConfig(self::XML_SEND_INVOICE_EMAIL);
    }

    public function isEnableShipOrders()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_SHIP_ORDERS);
    }

    public function isSendShipmentEmail()
    {
        return Mage::getStoreConfig(self::XML_SEND_SHIPMENT_EMAIL);
    }

    public function isEnableArchiveOrders()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_ARCHIVE_ORDERS);
    }

    public function isEnableDeleteOrders()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_DELETE_ORDERS);
    }

    public function isHideDeletedOrdersForCustomers()
    {
        return Mage::getStoreConfig(self::XML_HIDE_DELETED_ORDERS_FOR_CUSTOMERS);
    }

    public function isEnableDeleteOrdersCompletely()
    {
        return Mage::getStoreConfig(self::XML_ENABLE_DELETE_ORDERS_COMPLETLY);
    }

    public function getGridColumns()
    {
        $listColumns = Mage::getStoreConfig(self::XML_GRID_COLUMNS);
        $listColumns = explode(',', $listColumns);
        return $listColumns;
    }

    public function getCustomerGridColumns()
    {
        $listColumns = Mage::getStoreConfig(self::XML_CUSTOMER_GRID_COLUMNS);
        $listColumns = explode(',', $listColumns);
        return $listColumns;
    }

    public function getNumberComments()
    {
        return intval(Mage::getStoreConfig('mageworx_orderspro/general/number_comments'));
    }

    public function isShowThumbnails()
    {
        return Mage::getStoreConfig('mageworx_orderspro/general/show_thumbnails');
    }

    public function getThumbnailHeight()
    {
        return Mage::getStoreConfig('mageworx_orderspro/general/thumbnail_height');
    }

    public function addToOrderGroup($orderIds, $orderGroupId = 0)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();

        foreach ($orderIds as $orderId) {
            $connection->update($tablePrefix . 'sales_flat_order_grid', array('order_group_id' => intval($orderGroupId)), 'entity_id = ' . intval($orderId));
            $connection->update($tablePrefix . 'sales_flat_order', array('order_group_id' => intval($orderGroupId)), 'entity_id = ' . intval($orderId));
        }
        return count($orderIds);
    }

    public function deleteOrderCompletely($orderIds)
    {
        foreach ($orderIds as $orderId) {
            $this->deleteOrderCompletelyById($orderId);
        }
        return count($orderIds);
    }

    public function deleteOrderCompletelyById($order)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $write = $coreResource->getConnection('core_write');
        if (is_object($order)) {
            $orderId = $order->getId();
        } else {
            $order = Mage::getModel('sales/order')->load(intval($order), 'entity_id');
            $orderId = $order->getId();
        }

        if ($orderId) {
            // cancel
            try {
                $order->cancel()->save();
            } catch (Exception $e) {
            }
            // delete            
            if ($order->getQuoteId()) {
                $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_quote') . "` WHERE `entity_id`=" . $order->getQuoteId());
                $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_quote_address') . "` WHERE `quote_id`=" . $order->getQuoteId());
                $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_quote_item') . "` WHERE `quote_id`=" . $order->getQuoteId());
                $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_quote_payment') . "` WHERE `quote_id`=" . $order->getQuoteId());
            }
            $order->delete();
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_order_grid') . "` WHERE `entity_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_order_address') . "` WHERE `parent_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_order_item') . "` WHERE `order_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_order_payment') . "` WHERE `parent_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_payment_transaction') . "` WHERE `order_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_order_status_history') . "` WHERE `parent_id`=" . $orderId);

            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_invoice') . "` WHERE `order_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_creditmemo') . "` WHERE `order_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_flat_shipment') . "` WHERE `order_id`=" . $orderId);
            $write->query("DELETE FROM `" . $coreResource->getTableName('sales_order_tax') . "` WHERE `order_id`=" . $orderId);


            if (Mage::getConfig()->getModuleConfig('AW_Booking')->is('active', true)) {
                $write->query("DELETE FROM `" . $coreResource->getTableName('aw_booking_orders') . "` WHERE `order_id`=" . $orderId);
            }

        }
    }


    public function getUploadFilesPath($fileId, $createFolder = false)
    {
        // 3 byte -> 8 chars
        $fileId = '00000000' . $fileId;
        $fileId = substr($fileId, strlen($fileId) - 8, 8);
        $dir = substr($fileId, 0, 5);
        $file = substr($fileId, 5);

        $catalog = Mage::getBaseDir('media') . DS . 'orderspro' . DS;

        if ($createFolder && !file_exists($catalog)) {
            mkdir($catalog);
            //chmod($catalog, 777);
        }

        if ($createFolder && !file_exists($catalog . $dir . DS)) {
            mkdir($catalog . $dir . DS);
            //chmod($catalog . $dir . DS, 777);
        }

        return $catalog . $dir . DS . $file;
    }

    public function isUploadFile($fileId)
    {
        $file = $this->getUploadFilesPath($fileId, false);
        if (file_exists($file)) return $file; else return null;
    }

    public function getUploadFilesUrl($fileId, $fileName)
    {
        // orderspro/dl/file/id/1/file.png
        return $this->_getUrl('mageworx_orderspro/dl/') . 'file/id/' . $fileId . '/' . $fileName;
    }


    public function prepareFileSize($size)
    {

        if ($size >= 1048576) {
            return round($size / 1048576, 2) . ' ' . $this->__('MB');
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' ' . $this->__('KB');
        } else {
            return $size . ' ' . $this->__('B');
        }
    }


    public function processDownload($resource, $fileName)
    {
        $this->_resourceFile = $resource;

        $response = Mage::app()->getResponse();
        $response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $this->getContentType($fileName), true);

        if ($fileSize = $this->_getHandle()->streamStat('size')) {
            $response->setHeader('Content-Length', $fileSize);
        }
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->clearBody();
        $response->sendHeaders();

        $this->output();
    }

    protected function _getHandle()
    {
        if (!$this->_resourceFile) {
            Mage::throwException($this->__('Please set resource file and link type'));
        }
        if (is_null($this->_handle)) {
            $this->_handle = new Varien_Io_File();
            $this->_handle->open(array('path' => Mage::getBaseDir('var')));
            if (!$this->_handle->fileExists($this->_resourceFile, true)) {
                Mage::throwException($this->__('File does not exist'));
            }
            $this->_handle->streamOpen($this->_resourceFile, 'r');
        }
        return $this->_handle;
    }


    public function getContentType()
    {
        $this->_getHandle();
        if (function_exists('mime_content_type')) {
            return mime_content_type($this->_resourceFile);
        } else {
            return $this->getFileType($this->_resourceFile);
        }
        return $this->_contentType;
    }


    public function getFileType($fileName)
    {
        $ext = substr($fileName, strrpos($fileName, '.') + 1);
        $type = Mage::getConfig()->getNode('global/mime/types/x' . $ext);
        if ($type) {
            return $type;
        }
        return $this->_contentType;
    }

    public function output()
    {
        $handle = $this->_getHandle();
        while ($buffer = $handle->streamRead()) {
            print $buffer;
        }
    }


    public function sendOrderUpdateEmail($orders, $notifyCustomer = true, $comment = '', $filePath = null, $fileName = null)
    {
        $storeId = $orders->getStore()->getId();

        if (!Mage::helper('sales')->canSendOrderCommentEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails('sales_email/order_comment/copy_to', $storeId);
        $copyMethod = Mage::getStoreConfig('sales_email/order_comment/copy_method', $storeId);
        // Check if at least one recepient is found
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        // Retrieve corresponding email template id and customer name
        if ($orders->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig('sales_email/order_comment/guest_template', $storeId);
            $customerName = $orders->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig('sales_email/order_comment/template', $storeId);
            $customerName = $orders->getCustomerName();
        }

        $mailer = Mage::getModel('mageworx_orderspro/core_email_template_mailer');

        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($orders->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        // Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig('sales_email/order_comment/identity', $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order' => $orders,
                'comment' => $comment,
                'billing' => $orders->getBillingAddress()
            )
        );
        $mailer->send($filePath, $fileName);

        return $this;
    }

    protected function _getEmails($configPath, $storeId)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    public function invoiceOrder($order)
    {
        $savedQtys = array();
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getQtyToInvoice() > 0) {
                $savedQtys[$orderItem->getId()] = $orderItem->getQtyToInvoice();
            }
        }

        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
        if (!$invoice->getTotalQty()) { return false; };
        //Mage::register('current_invoice', $invoice);

        $invoice->setRequestedCaptureCase('online');

        $invoice->register();

        // if send email
        $sendEmailFlag = $this->isSendInvoiceEmail();
        if ($sendEmailFlag) {
            $invoice->setEmailSent(true);
        }

        $invoice->getOrder()->setCustomerNoteNotify($sendEmailFlag);
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();

        // if send email
        $invoice->sendEmail($sendEmailFlag, '');

        return $invoice;
    }

    public function invoiceOrderMass($orderIds)
    {
        $count = 0;
        foreach ($orderIds as $orderId) {
            $orderId = intval($orderId);
            if ($orderId > 0) {

                $order = Mage::getModel('sales/order')->load($orderId);
                if (!$order->getId()) continue;
                if (!$order->canInvoice()) continue;

                $invoice = $this->invoiceOrder($order);
                if ($invoice) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function shipOrder($orderIds)
    {
        $count = 0;
        foreach ($orderIds as $orderId) {
            $orderId = intval($orderId);
            if ($orderId > 0) {
                try {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    if (!$order->getId()) continue;
                    if ($order->getForcedDoShipmentWithInvoice()) continue;
                    if (!$order->canShip()) continue;

                    $savedQtys = array();
                    foreach ($order->getAllItems() as $orderItem) {
                        $savedQtys[$orderItem->getId()] = $orderItem->getQtyToShip();
                    }

                    $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
                    //Mage::register('current_shipment', $shipment);                
                    if (!$shipment) continue;
                    if (!$shipment->getTotalQty()) continue;

                    $shipment->register();

                    // if send email          
                    $sendEmailFlag = $this->isSendShipmentEmail();
                    if ($sendEmailFlag) {
                        $shipment->setEmailSent(true);
                    }

                    $shipment->getOrder()->setCustomerNoteNotify($sendEmailFlag);
                    $shipment->getOrder()->setIsInProcess(true);
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();
                    // if send email
                    $shipment->sendEmail($sendEmailFlag, '');
                    $count++;
                } catch (Exception $e) {
                }
            }
        }
        return $count;
    }

    public function changeStatusOrder($orderIds, $status, $comment = '', $isVisibleOnFront = 1, $isCustomerNotified = false)
    {
        $count = 0;
        foreach ($orderIds as $orderId) {
            $orderId = intval($orderId);
            if ($orderId > 0) {
                try {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    if (!$order->getId()) continue;
                    $response = false;

                    $order->addStatusHistoryComment($comment, $status)
                        ->setIsVisibleOnFront($isVisibleOnFront)
                        ->setIsCustomerNotified($isCustomerNotified);

                    if ($isCustomerNotified) {
                        $comment = trim(strip_tags($comment));
                        $order->sendOrderUpdateEmail($isCustomerNotified, $comment);
                    }


                    $order->save();
                    $count++;
                } catch (Exception $e) {
                }
            }
        }
        return $count;
    }

    // translate and QuoteEscape
    public function __js($str)
    {
        return $this->jsQuoteEscape(str_replace("\'", "'", $this->__($str)));
    }

    public function getAllPaymentMethods()
    {
        if (Mage::registry('payment_methods')) return Mage::registry('payment_methods');
        $payments = Mage::getSingleton('payment/config')->getAllMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $methods[$paymentCode] = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
        }
        Mage::register('payment_methods', $methods);
        return $methods;
    }

    public function getAllShippingMethods()
    {
        if (Mage::registry('shipping_methods')) return Mage::registry('shipping_methods');
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        $methods = array();
        foreach ($carriers as $code => $carriersModel) {
            $title = Mage::getStoreConfig('carriers/' . $code . '/title');
            if ($title) $methods[$code . '_' . $code] = $title;
        }
        Mage::register('shipping_methods', $methods);
        //print_r($methods); exit;
        return $methods;
    }

    public function getCustomerGroups()
    {
        if (Mage::registry('customer_groups')) return Mage::registry('customer_groups');
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $groups = array();
        foreach ($customerGroups as $data) {
            $groups[$data['value']] = $data['label'];
        }
        Mage::register('customer_groups', $groups);
        return $groups;
    }

    public function getOrderGroups()
    {
        if (Mage::registry('order_groups')) return Mage::registry('order_groups');
        $orderGroups = Mage::getResourceModel('mageworx_orderspro/order_group_collection')->load()->toOptionArray();
        Mage::register('order_groups', $orderGroups);
        return $orderGroups;
    }

    public function getShippedStatuses()
    {
        if (Mage::registry('shipped_statuses')) return Mage::registry('shipped_statuses');
        $statuses = array('1' => $this->__('Yes'), '0' => $this->__('No'));
        Mage::register('shipped_statuses', $statuses);
        return $statuses;
    }

    public function getEditedStatuses()
    {
        if (Mage::registry('edited_statuses')) return Mage::registry('edited_statuses');
        $statuses = array('1' => $this->__('Yes'), '0' => $this->__('No'));
        Mage::register('edited_statuses', $statuses);
        return $statuses;
    }

    public function getImgByItem($item)
    {
        $productId = $item->getProductId();
        //getRealProductType
        //getParentItem
        $product = Mage::getModel('catalog/product')->setStoreId($item->getStoreId())->load($productId);
        if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
            try {
                return Mage::helper('catalog/image')->init($product, 'thumbnail');
            } catch (Exception $e) {
                return false;
            }
        }
        if ($product->getTypeId() == 'configurable') {
            $childrens = $item->getChildrenItems();
            if (count($childrens) > 0) {
                $productId = $childrens[0]->getProductId();
                if ($productId) {
                    $product = Mage::getModel('catalog/product')->setStoreId($item->getStoreId())->load($productId);
                    if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
                        try {
                            return Mage::helper('catalog/image')->init($product, 'thumbnail');
                        } catch (Exception $e) {
                            return false;
                        }
                    }
                }
            }

//            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($productId);
//            if(count($parentIds) > 0){
//                $product = Mage::getModel('catalog/product')->setStoreId($item->getStoreId())->load($parentIds[0]);
//                if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
//                 return Mage::helper('catalog/image')->init($product, 'thumbnail');
//                }
//            }
        }
        return false;
    }

    public function isMagetoEnterprise()
    {
        $isEnterprise = false;
        $i = Mage::getVersionInfo();
        if ($i['major'] == 1) {
            if (method_exists('Mage', 'getEdition')) {
                if (Mage::getEdition() == Mage::EDITION_ENTERPRISE) $isEnterprise = true;
            } elseif ($i['minor'] > 7) {
                $isEnterprise = true;
            }
        }
        return $isEnterprise;
    }

    public function getMagetoVersion()
    {
        $i = Mage::getVersionInfo();
        if ($i['major'] == 1 && $this->isMagetoEnterprise()) $i['minor'] -= 5;
        return trim("{$i['major']}.{$i['minor']}.{$i['revision']}" . ($i['patch'] != '' ? ".{$i['patch']}" : "") . "-{$i['stability']}{$i['number']}", '.-');
    }

}