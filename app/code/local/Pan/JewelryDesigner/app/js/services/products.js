/* jshint laxcomma: true */

'use strict';

/**
 * Fetch products from our Magento Pan_JewelryDesigner_ApiController
 * by the product type (i.e., 'bracelets', 'charms', 'clips', etc.)
 */
angular.module('jewelryDesigner')
  .factory('Product', ['$resource',
    function ($resource) {
      return $resource('/jewelrydesigner/api/products', {}, {
        query: { method: 'GET', cache: true, params: { type: '@type' }, isArray: true },
      });
    }
]);
