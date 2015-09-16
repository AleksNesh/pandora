<?php

//if (!@class_exists('Xtento_CustomTrackers_Model_Shipping_Config')) {
if (!file_exists(Mage::getBaseDir() . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Xtento' . DS . 'CustomTrackers' . DS . 'Model' . DS . 'Shipping' . DS . 'Config.php')) {
    class Xtento_GridActions_Model_Rewrite_Shipping_Config extends Mage_Shipping_Model_Config
    {
        protected function _getCarrier($code, $config, $store = null)
        {
            if (!isset($config['model'])) {
                #throw Mage::exception('Mage_Shipping', 'Invalid model for shipping method: ' . $code);
                return false;
            }
            $modelName = $config['model'];

            /**
             * Added protection from not existing models usage.
             * Related with module uninstall process
             */
            try {
                $carrier = Mage::getModel($modelName);
            } catch (Exception $e) {
                Mage::logException($e);
                return false;
            }
            // More protection against not existing carriers - Added by XTENTO
            if (!$carrier) {
                return false;
            }
            $carrier->setId($code)->setStore($store);
            self::$_carriers[$code] = $carrier;
            return self::$_carriers[$code];
        }
    }
} else {
    class Xtento_GridActions_Model_Rewrite_Shipping_Config extends Xtento_CustomTrackers_Model_Shipping_Config
    {

    }
}
