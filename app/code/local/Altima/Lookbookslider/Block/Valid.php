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
class Altima_Lookbookslider_Block_Valid extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        $helper = Mage::helper('lookbookslider');
        $message = false;
           
        if($helper->canRun(false)) {
            return '';
        }
        
        if($helper->canRun(true)) {
            return base64_decode('PGRpdiBzdHlsZT0iYm9yZGVyOiAxcHggc29saWQgZ3JleTsgcGFkZGluZzogNXB4OyBtYXJnaW4tYm90dG9tOiA1cHg7IG1hcmdpbi10b3A6IDVweDsgdGV4dC1hbGlnbjogY2VudGVyIiA+VGhpcyBMb29rYm9vayBQcm9mZXNzaW9uYWwgZXh0ZW5zaW9uIGlzIHJ1bm5pbmcgb24gYSBkZXZlbG9wbWVudCBzZXJpYWwuIERvIG5vdCB1c2UgdGhpcyBzZXJpYWwgZm9yIHByb2R1Y3Rpb24gZW52aXJvbm1lbnRzLjwvZGl2Pg==');
        }

        return str_replace('[DOMAIN]', $_SERVER['SERVER_NAME'],  base64_decode('PGRpdiBzdHlsZT0iYm9yZGVyOiAzcHggc29saWQgcmVkOyBwYWRkaW5nOiA1cHg7IG1hcmdpbi1ib3R0b206IDE1cHg7IG1hcmdpbi10b3A6IDE1cHg7Ij5QbGVhc2UgZW50ZXIgYSB2YWxpZCBzZXJpYWwgZm9yIHRoZSBkb21haW4gIltET01BSU5dIiBpbiB5b3VyIGFkbWluaXN0cmF0aW9uIHBhbmVsLiBJZiB5b3UgZG9uJ3QgaGF2ZSBvbmUsIHBsZWFzZSBwdXJjaGFzZSBhIHZhbGlkIGxpY2Vuc2UgZnJvbSA8YSBocmVmPSJodHRwOi8vYmxvZy5hbHRpbWEubmV0LmF1L21hZ2VudG8tbG9va2Jvb2staG90c3BvdHMtZXh0ZW5zaW9uIj5odHRwOi8vYmxvZy5hbHRpbWEubmV0LmF1L21hZ2VudG8tbG9va2Jvb2staG90c3BvdHMtZXh0ZW5zaW9uPC9hPjxici8+PGJyLz5JZiB5b3UgaGF2ZSBlbnRlcmVkIGEgdmFsaWQgc2VyaWFsIGFuZCBzdGlsbCBleHBlcmllbmNlIGFueSBwcm9ibGVtIHBsZWFzZSB3cml0ZSB0byA8YSBjbGFzcz0iZW1haWwiIGhyZWY9Im1haWx0bzpzdXBwb3J0QGFsdGltYS5uZXQuYXUiPnN1cHBvcnRAYWx0aW1hLm5ldC5hdTwvYT48L2Rpdj4='));
    }
}

