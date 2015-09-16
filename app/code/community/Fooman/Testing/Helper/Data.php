<?php
class Fooman_Testing_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_section = null;
    protected $_group = null;
    protected $_field = null;

    /**
     * Setting current section of the config path
     *
     * @param string $section
     * @return void
     */
    public function setSection($section)
    {
        $this->_section = $section;
    }

    /**
     * Setting current group of the config path
     *
     * @param string $group
     * @return void
     */
    public function setGroup($group)
    {
        $this->_group = $group;
    }

    /**
     * Setting current field of the config path
     *
     * @param string $field
     * @return void
     */
    public function setField($field) {
        $this->_field = $field;
    }

    /**
     * Helper method for switching current extension by loading apropriate custom backend model if any, reiniting/reseting
     * everything
     *
     * @param boolean $activated
     * @param array $settings
     * @param string $backendModel
     * @return boolean
     */
    protected function _switchExtension($activated = true, $settings = array(), $backendModel = '')
    {
        $data = array();
        $data['field']= $this->_field;
        $data['value']= $activated;
        $data['path'] = $this->_section . '/' . $this->_group . '/' . $this->_field;
        $data['scope']= 'default';
        $data['scope_id'] ='0';
        $data['groups'][$this->_group]['fields'][$this->_field]['value']= $activated;

        if(!empty($settings)) {
            foreach($settings as $groupCode => $settingsGroup) {
                foreach($settingsGroup as $settingKey => $settingValue) {
                    $data['groups'][$groupCode]['fields'][$settingKey]['value']= $settingValue;
                    $configCollection = Mage::getModel('core/config_data')->getCollection()
                                                      ->AddFieldToFilter('scope', 'default')
                                                      ->AddFieldToFilter('scope_id', '0')
                                                      ->AddFieldToFilter('path', $this->_section . '/' . $groupCode . '/' .
                                                                         $settingKey)
                                                      ->load();
                    if ($configCollection->getFirstItem()) {
                        $configModel = $configCollection->getFirstItem();
                        $configModel->setValue($settingValue)->save();
                    } else {
                        $configModel = Mage::getModel('core/config_data');
                        $configModel->setData(array('scope' => 'default',
                                                    'scope_id' => '0',
                                                    'path' => $this->_section . '/' . $groupCode . '/' .
                                                        $settingKey,
                                                    'value' => $settingValue
                            ))->save();
                    }
                }
            }
        }

        $collection = Mage::getModel('core/config_data')
            ->getCollection()
            ->addFieldToFilter('path', $this->_section . '/' . $this->_group . '/' . $this->_field)
            ->addFieldToFilter('scope', $data['scope'])
            ->addFieldToFilter('scope_id', $data['scope_id']);
        $existingConfig = $collection->getFirstItem();
        
        if($backendModel) {
            $backendConfig = Mage::getModel($backendModel);
            $backendConfig->setData($data);
            if($existingConfig->getConfigId()) {
                $backendConfig->setConfigId($existingConfig->getConfigId());
            }
            $backendConfig->save();
        } else {
            $existingConfig->setData($data);
            $existingConfig->save();
        }
        
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        Mage::app()->resetAreas();
        //Mage::getSingleton('eav/config')->clear();
        return true;
    }

    /**
     * Turns extension ON and passes given settings and backend model name
     *
     * @param array $settings
     * @param string $backendModel
     * @return void
     */
    public function activateExtension($settings = array(), $backendModel = '')
    {
        $this->_switchExtension(true, $settings, $backendModel);
    }

    /**
     * Turns extension OFF and passes given backend model name
     *
     * @param string $backendModel
     * @return void
     */
    public function deactivateExtension($backendModel = '')
    {
        $this->_switchExtension(false, array(), $backendModel);
    }

    /**
     * Calls quote service, passes the given quote and submit the newly created order
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Order
     */
    protected function _submitOrder(Mage_Sales_Model_Quote $quote)
    {
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        return $service->getOrder();
    }

    /**
     * Creates quote and submits a new order
     *
     * @param int $store
     * @return Mage_Sales_Model_Order
     */
    public function placeOrder($store = 1)
    {
        $quote = $this->_createQuote($store);
        return $this->_submitOrder($quote);
    }


    /**
     * Creates quote
     *
     * @param int $store
     * @param int $customerId
     *
     * @throws Exception
     * @return Mage_Sales_Model_Quote
     */
    protected function _createQuote($store, $customerId=1)
    {
        $creator = Mage::getSingleton('foomantesting/create');
        $creator->resetSession();
        if ($creator->getQuote()->getId()) {
            throw new Exception('quote is not new');
        }
        $book = Mage::getModel('catalog/product')->setStoreId($store)->load('1');
        $johnDoe = Mage::getModel('customer/customer');
        $creator->getQuote()->setStoreId($store)->assignCustomer($johnDoe);
        $creator->getQuote()->addProduct($book);
        $billingAddress = $creator->getQuote()->getBillingAddress();
        $shippingAddress =  $creator->getQuote()->getShippingAddress();
        $addresses = array('billingAddress','shippingAddress');
        foreach ($addresses as $address) {

            $$address->setFirstname('John');
            $$address->setLastname('Doe');
            $$address->setStreet('1600 Pennsylvania Ave');
            $$address->setCity('Washington');
            $$address->setCountryId('US');
            $$address->setPostcode('20500');
            $$address->setRegionId('16');
            $$address->setTelephone('12345');
        }
        $shippingAddress->setShippingMethod('flatrate_flatrate');
        $shippingAddress->setCollectShippingRates(true);
        $creator->getQuote()->setBillingAddress($billingAddress);
        $creator->getQuote()->setShippingAddress($shippingAddress);

        $creator->getQuote()->getPayment()->importData(array('method'=>'checkmo'));
        $creator->getQuote()->setTotalsCollectedFlag(false);
        foreach ($creator->getQuote()->getAllAddresses() as $address) {
            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonnominal');
        }
        $creator->getQuote()->collectTotals();


        return $creator->getQuote();
    }

    /**
     * Creates invoice based on the given order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function processInvoice(&$order)
    {
        if ($order->canInvoice()) {
            return Mage::getModel('sales/order_invoice_api')
                ->create($order->getIncrementId(), array());
        }
    }

    /**
     * Creates shipment based on the given order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function processShipment(&$order)
    {
        return Mage::getModel('sales/order_shipment_api')
            ->create($order->getIncrementId(), array());
    }

    /**
     * Creates creditmemo based on the given order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function processCreditmemo(&$order)
    {
        if (version_compare(Mage::getVersion(), '1.6.0', '<')) {
            return Mage::getModel('foomantesting/order_creditmemo_api')
                ->create($order->getIncrementId(), array());
        } else {
            return Mage::getModel('sales/order_creditmemo_api')
                ->create($order->getIncrementId(), array());
        }
    }

    /**
     * Deletes any created order, invoice, shipment, creditmemo, quote, store data
     *
     * @return void
     */
    public function deleteOrdersAndQuotes()
    {
        if(!Mage::registry('isSecureArea')){
            Mage::register('isSecureArea', true);
        }
        /* @var $orders Mage_Sales_Model_Mysql4_Order_Collection */
        $orders = Mage::getModel('sales/order')->getCollection()->load();
        $orders->walk('delete');

        $quotes = Mage::getModel('sales/quote')->getCollection()->load();
        $quotes->walk('delete');

        $quotes = Mage::getModel('sales/order_invoice')->getCollection()->load();
        $quotes->walk('delete');

        $quotes = Mage::getModel('sales/order_shipment')->getCollection()->load();
        $quotes->walk('delete');

        $quotes = Mage::getModel('sales/order_creditmemo')->getCollection()->load();
        $quotes->walk('delete');

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $salesQuoteShippingRateTableName = $resource->getTableName('sales_flat_quote_shipping_rate');
        if(method_exists($connection,'truncateTable')) {
            $connection->truncateTable($salesQuoteShippingRateTableName);
        } else {
            $connection->truncate($salesQuoteShippingRateTableName);
        }

        for($i = 0; $i < 4; $i++) {
            Mage::getModel('eav/entity_store')->loadByEntityStore(
                                              Mage::getModel('eav/entity_type')->loadByCode('order')->getId(), $i)
                                                ->delete();
            Mage::getModel('eav/entity_store')->loadByEntityStore(
                                              Mage::getModel('eav/entity_type')->loadByCode('invoice')->getId(), $i)
                                                ->delete();
            Mage::getModel('eav/entity_store')->loadByEntityStore(
                                              Mage::getModel('eav/entity_type')->loadByCode('shipment')->getId(), $i)
                                                ->delete();
            Mage::getModel('eav/entity_store')->loadByEntityStore(
                                              Mage::getModel('eav/entity_type')->loadByCode('creditmemo')->getId(), $i)
                                                ->delete();
        }
    }

    /**
     * Generates the current store date with the given date() format
     *
     * @param string $format
     * @return string
     */
    public function _getDate($format = 'd-F-Y-H:i')
    {
        return date($format, Mage::app()->getLocale()->storeTimeStamp());
    }

    /**
     * Logs custom message into 'fooman_testing.log' file if $filename is not set
     *
     * @param string $message
     * @param string $filename
     * @return string
     */
    public function doLog($message, $filename = '')
    {
        if (empty($filename)) {
            Mage::log($message, Zend_Log::DEBUG, 'fooman_testing.log', true);
        } else {
            Mage::log($message, Zend_Log::DEBUG, $filename, true);
        }
    }

    /**
     * Enables all Magento cache types
     *
     * @return void
     */
    public function enableMagentoCaches()
    {
        $cacheTypes = array();
        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            $cacheTypes[] = $type->getId();
        }
        $allTypes = Mage::app()->useCache();

        $updatedTypes = 0;
        foreach ($cacheTypes as $code) {
            if (empty($allTypes[$code])) {
                $allTypes[$code] = 1;
                $updatedTypes++;
            }
        }
        if ($updatedTypes > 0) {
            Mage::app()->saveUseCache($allTypes);
        }
    }
    
    /**
     * Disable all Magento cache types
     *
     * @return void
     */
    public function disableMagentoCaches()
    {
        $cacheTypes = array();
        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            $cacheTypes[] = $type->getId();
        }
        $allTypes = Mage::app()->useCache();

        $updatedTypes = 0;
        foreach ($cacheTypes as $code) {
            $allTypes[$code] = 0;
            $updatedTypes++;
        }
        if ($updatedTypes > 0) {
            Mage::app()->saveUseCache($allTypes);
        }
    }

}