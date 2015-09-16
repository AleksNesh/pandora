<?php
/**
 * Create example cms page
 *
 * @copyright   Copyright (c) 2015 Alpine Consulting, Inc
 * @author      kirill.kosonogov@alpineinc.com
 */
 

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$_content = <<<CONTENT
<div class="first_block">
    <div class="container">
        <div class="line">
            <div class="col-sm-6 col-sm-offset-6 classic_bracelets">
                <h3>Classic bracelets</h3>
                <p>This is the original PANDORA bracelets with the PANDORA clasp in sterling silver, 14k gold, Rose or oxydized.</p>
            </div>
        </div>

        <div class="line">
            <div class="col-sm-6 col-sm-offset-6 col-sm-pull-1 leather_bracelets">
                <h3>Leather bracelets</h3>
                <p>Looking for a more casual style? Try one of our leather PANDORA bracelets to add some style and color to your look.</p>
            </div>
        </div>

        <div class="line">
            <div class="col-sm-6 col-sm-offset-6 essence_bracelets">
                <h3>Essence bracelets</h3>
                <p>A new type of bracelet to represent the ESSENCE of you! These bracelets are thinner and only hold the ESSENCE charms.</p>
            </div>
        </div>
    </div>
</div>
<div class="second_block">
    <div class="container">
        <div class="line">
            <div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-0">
                <img src="{{skin url='images/alpine/second_block_1.jpg'}}" alt="" />
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4">
                <img src="{{skin url='images/alpine/second_block_2.jpg'}}" alt="" />
            </div>
            <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-0">
                <img src="{{skin url='images/alpine/second_block_3.jpg'}}" alt="" />
            </div>
        </div>
    </div>
</div>
<div class="third_block">
    <div class="container">
        <div class="line">
            <div class="hidden-xs col-sm-12 col-md-6">
                <div class="mobile_wrapper">
                    <img src="{{skin url='images/alpine/third_block_1.jpg'}}" alt="" />
                    <div class="right_text">
                        <h4>Jewerly <span>collections</span></h4>
                        <p>Some rings are also part of a jewelry collection that includes matching rings, earrings, charms and necklaces.</p>
                    </div>
                </div>
            </div>
            <div class="hidden-xs col-sm-12 col-md-6">
                <div class="mobile_wrapper">
                    <img src="{{skin url='images/alpine/third_block_2.jpg'}}" alt="" />
                    <div class="left_text">
                        <h4>The <span>March</span> birthstone is <span>aquamarine</span></h4>
                        <p>Celebrate the month of March or a 19th anniversary by wearing a PANDORA birthday blooms ring or individual charms with the aquamarine gemstone!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="fourth_block">
    <div class="container">
        <div class="line">
            <h3 class="visible-sm visible-xs mobile_header">Ho we can help you?</h3>
            <div class="col-md-6 col-md-offset-6">
                <div class="container">
                    <div class="line">
                        <div class="col-xs-6"><img src="{{skin url='images/alpine/fourth_block_1.jpg'}}" alt="" /></div>
                        <div class="col-xs-6"><img src="{{skin url='images/alpine/fourth_block_2.jpg'}}" alt="" /></div>
                        <div class="col-xs-6"><img src="{{skin url='images/alpine/fourth_block_3.jpg'}}" alt="" /></div>
                        <div class="col-xs-6"><img src="{{skin url='images/alpine/fourth_block_4.jpg'}}" alt="" /></div>
                        <div class="col-xs-6"><img src="{{skin url='images/alpine/fourth_block_5.jpg'}}" alt="" /></div>
                        <div class="col-xs-6"><img src="{{skin url='images/alpine/fourth_block_6.jpg'}}" alt="" /></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="fifth_block">
    <div class="container">
        <div class="line">
            <div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-8">
                <h3>Show her you love her with the gift of</h3>
                <img src="{{skin url='images/alpine/pandora.png'}}" alt="" />
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consectetur consequatur distinctio error facilis illo ipsum mollitia porro repellendus tempora totam? Dignissimos dolorem libero recusandae velit. Corporis et minus quaerat quidem?</p>
            </div>
        </div>
    </div>
</div>
<div class="sixth_block hidden-xs">
    <div class="container">
        <div class="line">
            <div class="col-md-9">
                <ul class="slides">
                    <li class="item">
                        <img src="{{skin url='images/alpine/item.jpg'}}" alt="" />
                        <p>
                            <a href="#">Always in my heart jewerly gift set</a>
                            <span>$150.00</span>
                        </p>
                    </li>
                    <li class="item">
                        <img src="{{skin url='images/alpine/item.jpg'}}" alt="" />
                        <p>
                            <a href="#">PANDORA ring</a>
                            <span>$1050.00</span>
                        </p>
                    </li>
                    <li class="item">
                        <img src="{{skin url='images/alpine/item.jpg'}}" alt="" />
                        <p>
                            <a href="#">Sweet necklace</a>
                            <span>$50.00</span>
                        </p>
                    </li>
                    <li class="item">
                        <img src="{{skin url='images/alpine/item.jpg'}}" alt="" />
                        <p>
                            <a href="#">Jewerly bracelet</a>
                            <span>$10.00</span>
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    (function($){
        $('.slides').bxSlider({
            maxSlides: 3,
            minSlides: 2,
            pager: false,
            slideWidth: 230,
            slideMargin: 20
        });
    })(jQuery)
</script>
CONTENT;

$_pageData = array(
    'identifier' => 'alpine_cms_example',
    'title' => 'Example CMS Page',
    'is_active' => 1,
    'root_template' => 'one_column',
    'content_heading' => 'Example CMS Page',
    'stores' => array(0),
    'content' => $_content,
    'layout_update_xml' => '
<remove name="breadcrumbs"/>
<reference name="head">
    <action method="addItem"><type>skin_css</type><name>css/alpine/grid.css</name></action>
    <action method="addItem"><type>skin_css</type><name>css/alpine/common.css</name></action>
    <action method="addItem"><type>skin_css</type><name>css/alpine/local.css</name></action>
</reference>
'
);



$_page = Mage::getModel('cms/page')->load('alpine_cms_example', 'identifier');
if(!$_page->getId()){
    $_page->setData($_pageData);
    $_page->save();
}
$installer->endSetup();