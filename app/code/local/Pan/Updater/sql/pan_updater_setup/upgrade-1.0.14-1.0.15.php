<?php
/**
 * Simple module for updating system configuration data.
 *
 * @category    Pan
 * @package     Pan_Updater
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

// begin a transaction
$installer->startSetup();

// Footer Links CMS Static Block
$footerLinksContent = <<<FOOTER_LINKS_BLOCK_CONTENT
<ul>
<li><a href="{{store direct_url='sitemap/sitemap.xml'}}">sitemap</a></li>
<li><a href="{{store direct_url='privacy-policy'}}">privacy policy</a></li>
<li><a href="{{store direct_url='faq'}}">FAQ</a></li>
<li><a href="{{store direct_url='/'}}">Pandora Mall of America</a></li>
<li><a class="external" href="http://www.pandora.net/en-gb/stores/ridgedale-mall">Pandora Ridgedale Center</a></li>
<li><a class="external" href="http://www.pandora.net/en-us/stores/united-states/55113/pandora-rosedale-center">Pandora Rosedale Center</a></li>
</ul>
FOOTER_LINKS_BLOCK_CONTENT;

try {
    $cmsBlock = Mage::getModel('cms/block')->load('footer_links');
    if ($cmsBlock->isObjectNew()) {
        $cmsBlock->setIdentifier('footer_links')
                 ->setStores(array(0))
                 ->setIsActive(true)
                 ->setTitle('Footer Links');
    }
    $cmsBlock->setContent($footerLinksContent)->save();
} catch (Exception $e) {
    Mage::log('FROM ' . __FILE__ . ' LINE ' . __LINE__);
    Mage::log($e->getMessage());
}

// end transaction
$installer->endSetup();
