<?php

/**
 * Optimiseweb Redirects Model Redirector
 *
 * @package     Optimiseweb_Redirects
 * @author      Kathir Vel (sid@optimiseweb.co.uk)
 * @copyright   Copyright (c) 2014 Optimise Web
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Optimiseweb_Redirects_Model_Redirector
{

    /**
     * Redirect Function
     *
     * Looks at 404 pages and then loads up the csv file to see if a match exists
     *
     * @param Varien_Event_Observer $observer
     */
    public function doRedirects(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $actionName = $request->getActionName();
        $requestUrl = rtrim($request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri(), '/');

        if ($actionName == 'noRoute') {
            $this->doRedirectsLegacy($requestUrl);
            $this->doRedirects1($requestUrl);
            $this->doQueryStringRedirects($requestUrl);
        }
        return;
    }

    protected function doRedirectsLegacy($requestUrl)
    {
        if (Mage::getStoreConfig('optimisewebredirects/general/upload') AND file_exists(Mage::getBaseDir('media') . '/optimiseweb/redirects/' . Mage::getStoreConfig('optimisewebredirects/general/upload'))) {

            $redirectLines = file(Mage::getBaseDir('media') . '/optimiseweb/redirects/' . Mage::getStoreConfig('optimisewebredirects/general/upload'));

            foreach ($redirectLines AS $redirectLine) {

                $sourceDestination = explode(';', $redirectLine);

                if (count($sourceDestination) == 2) {
                    $sourceUrl = rtrim(trim($sourceDestination[0]), '/');
                    $destinationUrl = trim($sourceDestination[1]);

                    if ($sourceUrl == $requestUrl) {
                        $response = Mage::app()->getResponse();
                        $response->setRedirect($destinationUrl, 301);
                        $response->sendResponse();
                        exit;
                    }
                    continue;
                }
            }
        }
    }

    protected function doRedirects1($requestUrl)
    {
        if (Mage::getStoreConfig('optimisewebredirects/redirects1/upload') AND file_exists(Mage::getBaseDir('media') . '/optimiseweb/redirects/' . Mage::getStoreConfig('optimisewebredirects/redirects1/upload'))) {

            $redirectLines = file(Mage::getBaseDir('media') . '/optimiseweb/redirects/' . Mage::getStoreConfig('optimisewebredirects/redirects1/upload'));

            foreach ($redirectLines AS $redirectLine) {

                $sourceDestination = explode(Mage::getStoreConfig('optimisewebredirects/redirects1/delimiter'), $redirectLine);

                if (count($sourceDestination) == 3) {
                    $sourceUrl = rtrim(trim($sourceDestination[0]), '/');
                    $destinationUrl = trim($sourceDestination[1]);
                    $redirectCode = (int) trim($sourceDestination[2]);

                    $doRedirect = FALSE;

                    if ($sourceUrl == $requestUrl) {
                        $doRedirect = TRUE;
                    } elseif (strpos($sourceUrl, Mage::getStoreConfig('optimisewebredirects/redirects1/wildcardcharacter'))) {
                        $sourceUrl = str_replace(Mage::getStoreConfig('optimisewebredirects/redirects1/wildcardcharacter'), '', $sourceUrl);
                        if (strpos($requestUrl, $sourceUrl) === 0) {
                            $doRedirect = TRUE;
                        }
                    }
                    if ($doRedirect) {
                        $response = Mage::app()->getResponse();
                        $response->setRedirect($destinationUrl, $redirectCode);
                        $response->sendResponse();
                        exit;
                    }
                    continue;
                }
            }
        }
    }

    protected function doQueryStringRedirects($requestUrl)
    {
        if (Mage::getStoreConfig('optimisewebredirects/querystring/upload') AND file_exists(Mage::getBaseDir('media') . '/optimiseweb/redirects/' . Mage::getStoreConfig('optimisewebredirects/querystring/upload'))) {

            $query = parse_url($requestUrl);
            $queryUrl = $query['scheme'] . '://' . $query['host'] . $query['path'];
            $requestUrl = rtrim($queryUrl, '/');

            if (array_key_exists('query', $query)) {
                parse_str($query['query'], $queryParts);
            }

            if (isset($queryParts) && is_array($queryParts)) {

                $redirectLines = file(Mage::getBaseDir('media') . '/optimiseweb/redirects/' . Mage::getStoreConfig('optimisewebredirects/querystring/upload'));

                foreach ($redirectLines AS $redirectLine) {

                    $queryVarDestination = explode(Mage::getStoreConfig('optimisewebredirects/querystring/delimiter'), $redirectLine);

                    if (count($queryVarDestination) == 5) {
                        $sourceUrl = rtrim(trim($queryVarDestination[0]), '/');
                        $queryVar = trim($queryVarDestination[1]);
                        $queryValue = trim($queryVarDestination[2]);
                        $destinationUrl = trim($queryVarDestination[3]);
                        $redirectCode = (int) trim($queryVarDestination[4]);

                        $doRedirect = FALSE;

                        if ($sourceUrl == $requestUrl) {
                            $doRedirect = TRUE;
                        } elseif (strpos($sourceUrl, Mage::getStoreConfig('optimisewebredirects/querystring/wildcardcharacter'))) {
                            $sourceUrl = str_replace(Mage::getStoreConfig('optimisewebredirects/querystring/wildcardcharacter'), '', $sourceUrl);
                            if (strpos($requestUrl, $sourceUrl) === 0) {
                                $doRedirect = TRUE;
                            }
                        }
                        if ($doRedirect) {
                            if (array_key_exists($queryVar, $queryParts) AND ($queryParts[$queryVar] == $queryValue)) {
                                $response = Mage::app()->getResponse();
                                $response->setRedirect($destinationUrl, $redirectCode);
                                $response->sendResponse();
                                exit;
                            }
                        }
                        continue;
                    }
                }
            }
        }
    }

}
