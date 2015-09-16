/* jshint laxcomma: true */
'use strict';

angular.module('jewelryDesigner').controller('ReviewCtrl', ['$scope', '$rootScope', '$stateParams', '$document', 'wsBracelet',
  function($scope, $rootScope, $stateParams, $document, wsBracelet) {
    $scope.designStep = 'review';

    angular.element('.sidebar-toggle').addClass($scope.designStep)
      .removeClass('charms')
      .removeClass('bracelets')
      .removeClass('clips')
      .removeClass('active');

    $document.ready(function(){
      if (!angular.element('.loader').hasClass('hidden')) {
        angular.element('.loader').addClass('hidden');
      }
    });

    $scope.getCurrentBracelet = function(){
      var bracelet = PAN.DesignerWorkspace.getCurrentBracelet();
      return bracelet;
    };

    $scope.getBraceletLineItems = function(){
      var bracelet  = $scope.current_bracelet
        , products;

      if (bracelet !== null && bracelet !== undefined) {
        products = JSON.parse(JSON.stringify(bracelet.products));
        // remove the 'total_price' key from the cloned products object
        delete products.total_price;
      } else {
        products = null;
      }

      return products;
    };

    $scope.getBraceletSubtotal = function() {
      var bracelet    = $scope.current_bracelet
        , total_price = 0;

      if (bracelet !== null && bracelet !== undefined) {
        total_price = bracelet.products.total_price;
      }

      return total_price;
    };

    /**
     * Set the $scope variables after the functions above have been defined
     */
    $scope.current_bracelet = wsBracelet;
    $scope.line_items       = $scope.getBraceletLineItems();
    $scope.showReview       = ($scope.line_items !== null) ? true : false;
    $scope.total_price      = $scope.getBraceletSubtotal();

    // take action on broadcast message from a $rootScope.$emit('design_loaded');
    $rootScope.$on('design_loaded', function(){
      $scope.current_bracelet = $scope.getCurrentBracelet();
      $scope.line_items       = $scope.getBraceletLineItems();
      $scope.showReview       = ($scope.line_items !== null) ? true : false;
      $scope.total_price      = $scope.getBraceletSubtotal();
    });

    $scope.addToWishlist = function() {
      var productsAsJson = JSON.stringify($scope.line_items);

      jQuery.ajax({
          url: '/jewelrydesigner/api/addToWishlist'
        , type: 'POST'
        , dataType: 'json'
        , data: {
            products: productsAsJson
        }
      }).done(function(data){
        PAN.DesignerWorkspace.showMessage(data.message);
      });
    };

    $scope.addToCart = function() {
      var productsAsJson = JSON.stringify($scope.line_items);

      jQuery.ajax({
          url: '/jewelrydesigner/api/addToCart'
        , type: 'POST'
        , dataType: 'json'
        , data: {
            products: productsAsJson
        }
      }).done(function(data){
        if (data.error === false) {
          jQuery('.top-dropdowns .ajax-cart-update-wrapper').html(data.cartTop);
        }
        PAN.DesignerWorkspace.showMessage(data.message);
      });
    };

    $scope.isAdmin = function() {
      return jQuery('.designer_app_wrapper').hasClass('admin');
    };
  }
]);
