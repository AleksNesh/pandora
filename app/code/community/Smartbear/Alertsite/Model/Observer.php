<?php
class Smartbear_Alertsite_Model_Observer
{
    /**
     * Place the hook in order to land our alertsite block on the Dash.
     *
     * @param Varien_Event_Observer $observer
     * @return Smartbear_Alertsite_Model_Observer
     */
    public function coreBlockAbstractPrepareLayoutAfter(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('alertsite')->getConfig('alertsite_config', 'enabled') || strlen(trim(Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_user'))) < 1)
        {
            return $this;
        }

        if (Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index')
        {
            $block = $observer->getBlock();
            if ($block->getNameInLayout() === 'dashboard')
            {
                $block->getChild('topSearches')->setAlertsiteDashboardHook(true);
            }
        }
        return $this;
    }

    /**
     * Looks for the hook we set and adds our own custom block.
     *
     * @param Varien_Event_Observer $observer
     * @return Smartbear_Alertsite_Model_Observer
     */
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {

        if(!Mage::helper('alertsite')->getConfig('alertsite_config', 'enabled') || strlen(trim(Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_user'))) < 1)
        {
            return $this;
        }

        if (Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index')
        {
            if ($observer->getBlock()->getAlertsiteDashboardHook())
            {
                $html = $observer->getTransport()->getHtml(); // grab the html that's already rendered


                /** @var $dashboardBox Smartbear_Alertsite_Block_Adminhtml_Dashboard */
                $dashboardBox = $observer->getBlock()->getLayout()->createBlock('alertsite/adminhtml_dashboard');
                $dashboardBox->insert($observer->getBlock()->getLayout()->createBlock('core/template', 'alertsite.subheader')->setTemplate('alertsite/subheader.phtml'));

                $html .= $dashboardBox->toHtml();

                $observer->getTransport()->setHtml($html);
            }
        }

        return $this;
    }
}
