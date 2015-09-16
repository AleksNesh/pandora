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

// CMS Page content
$homePageContent = '<h2>Welcome to the Pandora Store</h2>
<p>Lorem ipsum dolor sit amet, autem assentior an vix, lucilius accusamus an ius. Alii soleat est no, te vim munere dolorum, diceret menandri mei cu. Mutat vulputate qui at, corpora scripserit vituperatoribus sed at. In everti numquam vis. Ea affert admodum adversarium eos, te alia mucius mel, est at erant similique interesset.<br /><br />Vim no persius albucius, ius solum altera in. Dolore offendit erroribus est te. Essent audire intellegebat ad pri, mucius menandri vix cu. Movet suscipit voluptua mea ad, te vim graece albucius. In apeirian molestiae omittantur qui.</p>';

$homePageLayouXmlUpdate = <<<HOME_PAGE_LAYOUT_XML_UPDATE
<!--
<reference name="content">
<block type="catalog/product_new" name="home.catalog.product.new" alias="product_new" template="catalog/product/new.phtml" after="cms_page">
    <action method="addPriceBlockType">
        <type>bundle</type>
        <block>bundle/catalog_product_price</block>
        <template>bundle/catalog/product/price.phtml</template>
    </action>
</block>
<block type="reports/product_viewed" name="home.reports.product.viewed" alias="product_viewed" template="reports/home_product_viewed.phtml" after="product_new">
    <action method="addPriceBlockType">
        <type>bundle</type>
        <block>bundle/catalog_product_price</block>
        <template>bundle/catalog/product/price.phtml</template>
    </action>
</block>
<block type="reports/product_compared" name="home.reports.product.compared" template="reports/home_product_compared.phtml" after="product_viewed">
    <action method="addPriceBlockType">
        <type>bundle</type>
        <block>bundle/catalog_product_price</block>
        <template>bundle/catalog/product/price.phtml</template>
    </action>
</block>
</reference>
<reference name="right">
<action method="unsetChild"><alias>right.reports.product.viewed</alias></action>
<action method="unsetChild"><alias>right.reports.product.compared</alias></action>
</reference>
-->
HOME_PAGE_LAYOUT_XML_UPDATE;


$cmsPages = array(
    'home' => array(
        'title'             => 'Welcome to the Pandora Store',
        'content'           => $homePageContent,
        'content_heading'   => '',
        'root_template'     => 'home_page',
        'meta_keywords'     => '',
        'meta_description'  => '',
        'layout_xml_update' => $homePageLayoutXmlUpdate,
    ),
);

try {
    foreach ($cmsPages as $key => $pageData) {
        $cmsPage = Mage::getModel('cms/page')->load($key);
        if ($cmsPage->isObjectNew()) {
            $cmsPage->setIdentifier($key)
                     ->setStores(array(0))
                     ->setIsActive(true)
                     ->setTitle($pageData['title']);
        }

        $cmsPageData = array(
            'title'               => $pageData['title'],
            'root_template'       => $pageData['root_template'],
            'layout_xml_update'   => $pageData['layout_xml_update'],
            'meta_keywords'       => 'meta,keywords',
            'meta_description'    => 'meta description',
            'identifier'          => $key,
            'content_heading'     => $pageData['content_heading'],
            'content'             => $pageData['content']
        );

        $cmsPage->setData($cmsPageData)->save();
    }
} catch (Exception $e) {
    Mage::log('FROM ' . __FILE__ . ' LINE ' . __LINE__);
    Mage::log($e->getMessage());
}


// end transaction
$installer->endSetup();
