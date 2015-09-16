<?php 
class Smartbear_Alertsite_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_CONFIG_PATH = 'alertsite/';

    public function getConfig($section, $key, $flag = false) {
        $path = self::XML_CONFIG_PATH . $section . '/' . $key;

        if ($flag) {
            return Mage::getStoreConfigFlag($path);
        } else {
            return Mage::getStoreConfig($path);
        }
    }

    public function isSetup(){
        return !(strlen(trim($this->getConfig('alertsite_config', 'alertsite_user'))) == 0 || strlen(trim($this->getConfig('alertsite_config', 'alertsite_pass'))) == 0);
    }

    public function getBenchmarkUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/alertsite/benchmark');
    }

    public function getScatterPlotUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/alertsite/scatterplot');
    }

}
