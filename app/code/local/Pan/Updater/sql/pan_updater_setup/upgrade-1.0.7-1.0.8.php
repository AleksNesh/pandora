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
$footerLinksContent = '<ul>
<li><a href="{{store direct_url=\'sitemap.xml\'}}">sitemap</a></li>
<li><a href="{{store direct_url=\'privacy-policy\'}}">privacy policy</a></li>
<li><a href="{{store direct_url=\'faq\'}}">FAQ</a></li>
<li><a href="{{store direct_url=\'/\'}}">Pandora Mall of America</a></li>
<li><a class="external" href="http://www.pandora.net/en-gb/stores/ridgedale-mall">Pandora Ridgedale Center</a></li>
<li><a class="external" href="http://www.pandora.net/en-us/stores/united-states/55113/pandora-rosedale-center">Pandora Rosedale Center</a></li>
</ul>';


// Getting Started CMS Static Block
$gettingStartedContent = '<p><img src="{{media url="wysiwyg/homepage-callouts.png"}}" alt="" /></p>';

// Latest Blog Post CMS Static Block
$latestBlogPostContent = '<h2>Latest from our Blog</h2>
<p><img src="{{media url="wysiwyg/blog-image.jpg"}}" alt="" /></p>
<h4>Take Flight with Butterflies</h4>
<p>Lorem ipsum dolor sit amet, autem assentior an vix, lucilius accusamus an ius. Alii soleat est...</p>
<p><a href="/necklace">Read more...</a></p>';

// Footer Security CMS Static Block
$footerSecurityContent = '<h3>Be Secure: <img src="{{media url="wysiwyg/secure.jpg"}}" alt="" /></h3>';

// Footer Social Media Links CMS Static Block
$footerSocialContent = '<h3>Be Social: <img src="{{media url="wysiwyg/facebook.jpg"}}" alt="" /> <img src="{{media url="wysiwyg/twitter.jpg"}}" alt="" /> <img src="{{media url="wysiwyg/pinterest.jpg"}}" alt="" /> <img src="{{media url="wysiwyg/google.jpg"}}" alt="" /> <img src="{{media url="wysiwyg/youtube.jpg"}}" alt="" /></h3>';

$cmsBlocks = array(
    'footer_links'        => array('title' => 'Footer Links', 'content' => $footerLinksContent),
    'getting-started'     => array('title' => 'Getting Started with Pandora', 'content' => $gettingStartedContent),
    'recent-post'         => array('title' => 'Latest Blog Post', 'content' => $latestBlogPostContent),
    'footer-security'     => array('title' => 'Footer Security Block', 'content' => $footerSecurityContent),
    'footer-social'       => array('title' => 'Footer Social Media Links', 'content' => $footerSocialContent),
);

try {
    foreach ($cmsBlocks as $key => $blockData) {
        $cmsBlock = Mage::getModel('cms/block')->load($key);
        if ($cmsBlock->isObjectNew()) {
            $cmsBlock->setIdentifier($key)
                     ->setStores(array(0))
                     ->setIsActive(true)
                     ->setTitle($blockData['title']);
        }
        $cmsBlock->setContent($blockData['content'])->save();
    }
} catch (Exception $e) {
    Mage::log('FROM ' . __FILE__ . ' LINE ' . __LINE__);
    Mage::log($e->getMessage());
}


// end transaction
$installer->endSetup();
