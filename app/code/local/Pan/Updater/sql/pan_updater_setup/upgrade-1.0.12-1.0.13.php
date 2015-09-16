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

// Social Links CMS Static Block
$socialLinksContent = <<<SOCIAL_LINKS_BLOCK_CONTENT
<!-- AddThis Follow BEGIN -->
<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
    <a class="addthis_button_facebook_follow"  addthis:userid="PandoraMallOfAmerica">
        <img src="{{skin url='images/addthis_facebook.png'}}" width="34" height="34" alt="Follow us on Facebook" />
    </a>
    <a class="addthis_button_twitter_follow" addthis:userid="PandoraMOA">
        <img src="{{skin url='images/addthis_twitter.png'}}" width="34" height="34" alt="Follow us on Twitter" />
    </a>
    <a class="addthis_button_pinterest_follow" addthis:userid="pandoramoa">
        <img src="{{skin url='images/addthis_pinterest.png'}}" width="34" height="34" alt="Follow us on Pinterest" />
    </a>
    <a class="addthis_button_google_follow" addthis:userid="102731984563877776088">
        <img src="{{skin url='images/addthis_google-plus.png'}}" width="34" height="34" alt="Follow us on Google+" />
    </a>
    <a class="addthis_button_youtube_follow" addthis:userid="dannyboy830">
        <img src="{{media url='wysiwyg/youtube.jpg'}}" width="34" height="34" alt="Follow us on YouTube" />
    </a>
</div>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-536002b20b3319cf"></script>
<!-- AddThis Follow END -->
SOCIAL_LINKS_BLOCK_CONTENT;

$addThisContent = <<<ADDTHIS_LINKS_BLOCK_CONTENT
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
    <a class="addthis_button_facebook">
        <img src="<?php echo $this->getSkinUrl('images/addthis_facebook.png'); ?>" width="34" height="34" alt="Share on Facebook" />
    </a>
    <a class="addthis_button_twitter">
        <img src="<?php echo $this->getSkinUrl('images/addthis_twitter.png'); ?>" width="34" height="34" alt="Share on Twitter" />
    </a>
    <a class="addthis_button_pinterest_share">
        <img src="<?php echo $this->getSkinUrl('images/addthis_pinterest.png'); ?>" width="34" height="34" alt="Share on Pintrest" />
    </a>
    <a class="addthis_button_google_plusone_share">
        <img src="<?php echo $this->getSkinUrl('images/addthis_google-plus.png'); ?>" width="34" height="34" alt="Share on Google+" />
    </a>
    <a class="addthis_button_email">
        <img src="<?php echo $this->getSkinUrl('images/addthis_email.png'); ?>" width="34" height="34" alt="Share via Email" />
    </a>
    <a class="addthis_button_print">
        <img src="<?php echo $this->getSkinUrl('images/addthis_print.png'); ?>" width="34" height="34" alt="Print this" />
    </a>
</div>
<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-536002b20b3319cf"></script>
<!-- AddThis Button END -->
ADDTHIS_LINKS_BLOCK_CONTENT;

try {
    $cmsBlock1 = Mage::getModel('cms/block')->load('footer-social');
    if ($cmsBlock1->isObjectNew()) {
        $cmsBlock1->setIdentifier('footer-social')
                 ->setStores(array(0))
                 ->setIsActive(true)
                 ->setTitle('Footer Social Media Links');
    }
    $cmsBlock1->setContent($socialLinksContent)->save();

    $cmsBlock2 = Mage::getModel('cms/block')->load('shopper_product_addthis');
    if ($cmsBlock2->isObjectNew()) {
        $cmsBlock2->setIdentifier('shopper_product_addthis')
                 ->setStores(array(0))
                 ->setIsActive(true)
                 ->setTitle('AddThis Links');
    }
    $cmsBlock2->setContent($addThisContent)->save();

} catch (Exception $e) {
    Mage::log('FROM ' . __FILE__ . ' LINE ' . __LINE__);
    Mage::log($e->getMessage());
}


// end transaction
$installer->endSetup();
