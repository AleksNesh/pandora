<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{   
    /**
     * Add HEAD Item First
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Altima_Lookbookslider_Block_Page_Html_Head
     */
    public function addFirst($type, $name, $params=null, $if=null, $cond=null)
    {
        $_item = array(
            $type.'/'.$name => array(
                'type'   => $type,
                'name'   => $name,
                'params' => $params,
                'if'     => $if,
                'cond'   => $cond)
            );
        $_head = $this->__getHeadBlock();
        if (is_object($_head)) {
            $_itemList = $_head->getData('items');
            $_itemList = array_merge($_item, $_itemList);

            $_head->setData('items', $_itemList);
        }
    }

    /**
     * Add HEAD Item Last
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Altima_Lookbookslider_Block_Page_Html_Head
     */
    public function addLast($type, $name, $params=null, $if=null, $cond=null)
    {
        $_item = array(
            $type.'/'.$name => array(
                'type'   => $type,
                'name'   => $name,
                'params' => $params,
                'if'     => $if,
                'cond'   => $cond)
            );
        $_head = $this->__getHeadBlock();
        if (is_object($_head)) {
            $_itemList = $_head->getData('items');
            $_itemList = array_merge($_itemList, $_item);

            $_head->setData('items', $_itemList);
        }
    }
    /**
     * Add HEAD Item before
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Altima_Lookbookslider_Block_Page_Html_Head
     */
    public function addBefore($type, $name, $before=null, $params=null, $if=null, $cond=null)
    {
        if ($before) {
            $_backItem = array();
            $_searchStatus = false;
            $_searchKey = $type.'/'.$before;
            $_head = $this->__getHeadBlock();
            if (is_object($_head)) {
                $_itemList = $_head->getData('items');
                if (is_array($_itemList)) {
                    $keyList = array_keys($_itemList);
                    foreach ($keyList as &$_key) {
                        if ($_searchKey == $_key) {
                            $_searchStatus = true;
                        }

                        if ($_searchStatus) {
                            $_backItem[$_key] = $_itemList[$_key];
                            unset($_itemList[$_key]);
                        }
                    }
                }

                if ($type==='skin_css' && empty($params)) {
                    $params = 'media="all"';
                }
                $_itemList[$type.'/'.$name] = array(
                    'type'   => $type,
                    'name'   => $name,
                    'params' => $params,
                    'if'     => $if,
                    'cond'   => $cond,
                );

                if (is_array($_backItem)) {
                    $_itemList = array_merge($_itemList, $_backItem);
                }
                $_head->setData('items', $_itemList);
            }
        }
    }

    /**
     * Add HEAD Item After
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Altima_Lookbookslider_Block_Page_Html_Head
     */
    public function addAfter($type, $name, $after=null, $params=null, $if=null, $cond=null)
    {
        if ($after) {
            $_backItem = array();
            $_searchStatus = false;
            $_searchKey = $type.'/'.$after;
            $_head = $this->__getHeadBlock();
            if (is_object($_head)) {
                $_itemList = $_head->getData('items');
                if (is_array($_itemList)) {
                    $keyList = array_keys($_itemList);
                    foreach ($keyList as &$_key) {
                        if ($_searchStatus) {
                            $_backItem[$_key] = $_itemList[$_key];
                            unset($_itemList[$_key]);
                        }
                        if ($_searchKey == $_key) {
                            $_searchStatus = true;
                        }
                    }
                }

                if ($type==='skin_css' && empty($params)) {
                    $params = 'media="all"';
                }
                $_itemList[$type.'/'.$name] = array(
                    'type'   => $type,
                    'name'   => $name,
                    'params' => null,
                    'if'     => null,
                    'cond'   => null,
                );

                if (is_array($_backItem)) {
                    $_itemList = array_merge($_itemList, $_backItem);
                }
                $_head->setData('items', $_itemList);
            }
        }
    }

    /*
     * Get head block
     */
    private function __getHeadBlock() {
        return Mage::getSingleton('core/layout')->getBlock('head');
    }
}