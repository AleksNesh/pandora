<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-06-09T19:15:18+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Observer/Cron/Config.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Observer_Cron_Config extends Mage_Core_Model_Abstract
{
    /**
     * Add cronjobs to the Magento config dynamically before schedule generates/cron dispatches and ONLY for cron.php/cron.sh calls
     */
    public function addCronjobsToConfig($observer)
    {
        // Add export cronjobs to config
        $this->injectCronjobsIntoConfig(true);
        // Call original observer
        Mage::getModel('cron/observer')->dispatch($observer);
    }

    /**
     * Add cronjobs to the Magento config dynamically - just required if the AOE Scheduler extension is used, so we can see them in the backend
     */
    public function addCronjobsToConfigAoeScheduler($observer)
    {
        if (Mage::app()->getRequest() && in_array(Mage::app()->getRequest()->getControllerName(), array('scheduler', 'cron', 'timeline'))) {
            $this->injectCronjobsIntoConfig();
        }
    }

    public function injectCronjobsIntoConfig($cronExecution = false)
    {
        try {
            if (Mage::registry('xt_cron_injected') !== null) {
                return $this;
            }
            Mage::register('xt_cron_injected', true);
            if ($cronExecution) {
                // Dispatch "cron has been executed" event
                Mage::dispatchEvent('xtento_cronjob_execution', array());
            }
            if (!Mage::helper('xtento_orderexport')->getModuleEnabled() || !Mage::helper('xtento_orderexport')->isModuleProperlyInstalled()) {
                return $this;
            }
            $newJobs = new SimpleXMLElement('<?xml version="1.0"?><config><crontab><jobs></jobs></crontab></config>');
            $jobs = $newJobs->crontab->jobs;
            // Load profiles and add cronjobs
            $profileCollection = Mage::getModel('xtento_orderexport/profile')->getCollection();
            $profileCollection->addFieldToFilter('enabled', 1); // Profile enabled
            $profileCollection->addFieldToFilter('cronjob_enabled', 1); // Cronjob enabled
            foreach ($profileCollection as $profile) {
                if ($profile->getCronjobFrequency() == Xtento_OrderExport_Model_Observer_Cronjob::CRON_CUSTOM || ($profile->getCronjobFrequency() == '' && $profile->getCronjobCustomFrequency() !== '')) {
                    // Custom cron expression
                    $cronFrequencies = $profile->getCronjobCustomFrequency();
                    if (empty($cronFrequencies)) {
                        continue;
                    }
                    $cronFrequencies = array_unique(explode(";", $cronFrequencies));
                    $cronCounter = 0;
                    foreach ($cronFrequencies as $cronFrequency) {
                        if (empty($cronFrequency)) {
                            continue;
                        }
                        $cronCounter++;
                        $job = $jobs->addChild('xtento_orderexport_profile_' . $profile->getId() . '_cron_' . $cronCounter);
                        $job->addChild('schedule')->addChild('cron_expr', $cronFrequency);
                        $job->addChild('run')->addChild('model', 'xtento_orderexport/observer_cronjob::export');
                    }
                } else {
                    // No custom cron expression
                    $cronFrequency = $profile->getCronjobFrequency();
                    if (empty($cronFrequency)) {
                        continue;
                    }
                    $job = $jobs->addChild('xtento_orderexport_profile_' . $profile->getId());
                    $job->addChild('schedule')->addChild('cron_expr', $cronFrequency);
                    $job->addChild('run')->addChild('model', 'xtento_orderexport/observer_cronjob::export');
                }
            }
            // Done adding cronjobs, extend original cron config
            $origJobs = new Varien_Simplexml_Config('<?xml version="1.0"?><config><crontab>' . Mage::getConfig()->getNode('crontab/jobs')->asXML() . '</crontab></config>');
            $newCronConfig = new Varien_Simplexml_Config($newJobs->asXML());
            $newCronConfig->extend($origJobs);
        } catch (Exception $e) {
            Mage::log('Exception for _addCronjobsToConfig(): ' . $e->getMessage(), null, 'xtento_orderexport_cron.log', true);
            return $this;
        }
        // Reset original cronjobs and inject our custom cron config with our cronjobs on first position
        $node = Mage::getConfig()->getNode('crontab');
        unset($node->jobs);
        Mage::getConfig()->extend($newCronConfig, true);
        // Double check cronjob configuration
        $jobNode = Mage::getConfig()->getNode('crontab/jobs');
        if (!$jobNode || !$jobNode->children() || (method_exists($jobNode->children(), 'count') && $jobNode->children()->count() === 0)) {
            // There was a problem extending the config, restore original config.
            Mage::getConfig()->extend($origJobs, true);
        }
    }
}
