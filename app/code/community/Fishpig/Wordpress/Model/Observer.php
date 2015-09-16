<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
class Fishpig_Wordpress_Model_Observer extends Varien_Object
{
	/**
	 * Flag used to ensure observers only run once per cycle
	 *
	 * @var static array
	 */
	static protected $_singleton = array();

	/**
	 * Save the associations
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */	
	public function saveAssociationsObserver(Varien_Event_Observer $observer)
	{
		if (!$this->_observerCanRun(__METHOD__)) {
			return false;
		}

		try {
			Mage::helper('wordpress/associations')->processObserver($observer);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
		}
	}
	
	/**
	 * Inject links into the top navigation
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function injectTopmenuLinksObserver(Varien_Event_Observer $observer)
	{
		if (!$this->_observerCanRun(__METHOD__)) {
			return false;
		}
		
		if (Mage::getStoreConfigFlag('wordpress/menu/enabled')) {
			return $this->injectTopmenuLinks($observer->getEvent()->getMenu());
		}
	}

	/**
	 * Inject links into the Magento topmenu
	 *
	 * @param Varien_Data_Tree_Node $topmenu
	 * @return bool
	 */
	public function injectTopmenuLinks($topmenu, $menuId = null)
	{
		if (is_null($menuId)) {
			$menuId = Mage::getStoreConfig('wordpress/menu/id');
		}

		if (!$menuId) {
			return false;
		}

		$menu = Mage::getModel('wordpress/menu')->load($menuId);		
		
		if (!$menu->getId()) {
			return false;
		}

		return $menu->applyToTreeNode($topmenu);
	}

	/**
	 * Inject links into the Magento topmenu
	 *
	 * @param Varien_Data_Tree_Node $topmenu
	 * @return bool
	 */	
	public function injectXmlSitemapLinksObserver(Varien_Event_Observer $observer)
	{
		if (!$this->_observerCanRun(__METHOD__)) {
			return false;
		}

		$sitemap = $observer
			->getEvent()
				->getSitemap();

		$appEmulation = Mage::getSingleton('core/app_emulation');
		$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($sitemap->getStoreId());

		if (!Mage::getStoreConfigFlag('wordpress/module/enabled', $sitemap->getStoreId())) {
			return false;
		}

		$sitemapFilename = Mage::getBaseDir() . '/' . ltrim($sitemap->getSitemapPath() . $sitemap->getSitemapFilename(), '/' . DS);
		
		if (!file_exists($sitemapFilename)) {
			return $this;
		}
		
		$xml = trim(file_get_contents($sitemapFilename));
		
		// Trim off trailing </urlset> tag so we can add more
		$xml = substr($xml, 0, -strlen('</urlset>'));

		// Add the blog homepage
		$xml .= sprintf(
			'<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
			htmlspecialchars(Mage::helper('wordpress')->getUrl()),
			Mage::getSingleton('core/date')->gmtDate('Y-m-d'),
			'daily',
			'1.0'
		);
					
		$posts = Mage::getResourceModel('wordpress/post_collection')
			->setFlag('include_all_post_types', true)
			->addIsViewableFilter()
			->setOrderByPostDate()
			->load();
		
		foreach($posts as $post) {
			$xml .= sprintf(
				'<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
				htmlspecialchars($post->getUrl()),
				$post->getPostModifiedDate('Y-m-d'),
				'monthly',
				'0.5'
			);
		}
		
		$pages = Mage::getResourceModel('wordpress/page_collection')
			->addIsViewableFilter()
			->setOrderByPostDate()
			->load();
			
		foreach($pages as $page) {
			$xml .= sprintf(
				'<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
				htmlspecialchars($page->getUrl()),
				$page->getPostModifiedDate('Y-m-d'),
				'monthly',
				'0.8'
			);
		}

		$xml .= '</urlset>';
		
		@file_put_contents($sitemapFilename, $xml);

		$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

		return $this;
	}
	
	/**
	 * Determine whether the observer method can run
	 * This stops methods being called twice in a single cycle
	 *
	 * @param string $method
	 * @return bool
	 */
	protected function _observerCanRun($method)
	{
		if (!isset(self::$_singleton[$method])) {
			self::$_singleton[$method] = true;
			
			return true;
		}
		
		return false;
	}
}
