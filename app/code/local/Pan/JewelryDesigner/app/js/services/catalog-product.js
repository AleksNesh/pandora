/* jshint laxcomma: true */

'use strict';

/**
 * Fetch products from our Magento Pan_JewelryDesigner_ApiController
 * by the product type (i.e., 'bracelets', 'charms', 'clips', etc.)
 */
angular.module('jewelryDesigner')
  .factory('CatalogProduct', ['$http',
    function ($http) {
      var CatalogProduct = function(productType, limit){
        this.items        = [];
        this.dataItems    = [];

        this.colors       = [];
        this.themes       = [];
        this.materials    = [];
        this.sizes        = [];

        this.busy         = false;
        this.productType  = productType || 'bracelets';
        this.limit        = limit || 20;
        this.offset       = 0;

        var isAdmin = angular.element('.designer_app_wrapper').hasClass('admin');
        this.inAdminArea  = isAdmin;
        this.formKey      = (isAdmin) ? angular.element('.designer_app_wrapper').data('form-key') : null;
        this.secretKey    = (isAdmin) ? angular.element('.designer_app_wrapper').data('secret-key') : null;
        this.httpMethod   = 'GET';
      };

      CatalogProduct.prototype.getAll = function(){
        var that    = this // keep track of scope
          , url     = '/jewelrydesigner/api/products/type/' + this.productType;

        $http.get(url).success(function(data) {
          for (var i = 0; i < data.length; i++) {
            var item = data[i];

            if (item.color instanceof Array) {
              angular.forEach(item.color, function(c){
                if (item.color !== null && that.colors.indexOf(c) == -1 ) {
                  that.colors.push(c);
                }
              });
            } else {
              if (item.color !== null && that.colors.indexOf(item.color) == -1 ) {
                that.colors.push(item.color);
              }
            }

            if (item.theme instanceof Array) {
              angular.forEach(item.theme, function(t){
                if (item.theme !== null && that.themes.indexOf(t) == -1 ) {
                  that.themes.push(t);
                }
              });
            } else {
              if (item.theme !== null && that.themes.indexOf(item.theme) == -1 ) {
                that.themes.push(item.theme);
              }
            }

            if (item.material instanceof Array) {
              angular.forEach(item.material, function(m){
                if (item.material !== null && that.materials.indexOf(m) == -1 ) {
                  that.materials.push(m);
                }
              });
            } else {
              if (item.material !== null && that.materials.indexOf(item.material) == -1 ) {
                that.materials.push(item.material);
              }
            }

            this.dataItems.push(item);
          }
        }.bind(this));
      };

      CatalogProduct.prototype.nextPage = function(){
        if (this.busy) return;
        this.busy = true;

        var url = '/jewelrydesigner/api/products/type/'+this.productType+'/limit/'+this.limit+'/offset/'+this.offset;

        $http.get(url).success(function(data) {
          for (var i = 0; i < data.length; i++) {
            this.items.push(data[i]);
          }
          this.offset = this.items.length;
          this.busy = false;
        }.bind(this));
      };

      return CatalogProduct;
    }
]);
