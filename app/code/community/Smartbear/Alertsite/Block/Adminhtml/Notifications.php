<?php
/**
 * SL_Signaturelink_Block_Adminhtml_Notifications
 * Notifications block. Displays a note at the top of adminhtml pages.
 *
 * @category    Signaturelink
 * @package     SL_Signaturelink
 */
class Smartbear_Alertsite_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Get x management url
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', array('section' => 'alertsite'));
    }

    /**
     * Check to see if config options are set
     * @return bool
     */
    public function isRequiredSettingsNotification()
    {
        if(!Mage::helper('alertsite')->getConfig('alertsite_config', 'enabled'))
        {
            return false;
        }

		$test = (strlen(trim(Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_user'))) == 0 || strlen(trim(Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_pass'))) == 0);
		return $test;
    }
}
