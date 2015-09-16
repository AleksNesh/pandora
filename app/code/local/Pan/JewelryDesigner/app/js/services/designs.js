/* jshint laxcomma: true */

'use strict';

/**
 * Fetch products from our Magento Pan_JewelryDesigner_ApiController
 * by the product type (i.e., 'bracelets', 'charms', 'clips', etc.)
 */
angular.module('jewelryDesigner')
  .factory('Design', ['$resource', '$document',
    function ($resource, $document) {
      var inAdminArea = false
        , resourceUrl = '/jewelrydesigner/api/designs/'
        , formKey     = null
        , httpMethod  = 'GET';

      $document.ready(function(){
        inAdminArea = jQuery('.designer_app_wrapper').hasClass('admin');
        if (inAdminArea) {
          httpMethod  = 'POST';
          formKey     = jQuery('.designer_app_wrapper').data('form-key');
          resourceUrl = '/jewelrydesigner/adminhtml_api/designs/';
        }
      });

      return $resource(resourceUrl, {}, {
        query: { method: httpMethod, cache: true, params: { form_key: formKey, inspirations: '@inspirations'}, isArray: true },
        get: { method: httpMethod, cache: true, params: { id: '@id', form_key: formKey }, isArray: false },
      });
    }
]);
