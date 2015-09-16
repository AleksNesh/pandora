/* jshint laxcomma: true */

'use strict';

/**
 * Inspiration Bracelets Controller
 */
angular.module('jewelryDesigner').controller('InspirationsCtrl', ['$scope', '$state', '$stateParams', '$rootScope', '$document', 'Design',
  function($scope, $state, $stateParams, $rootScope, $document, Design) {

    angular.element('.main-container').removeClass('hero-splash');

    $document.ready(function(){
      if (!angular.element('.loader').hasClass('hidden')) {
        angular.element('.loader').addClass('hidden');
      }
    });

    $scope.title        = 'Inspiration Bracelets';
    $scope.inAdminArea  = false;
    $scope.formKey      = null;

    $document.ready(function(){
      $scope.inAdminArea = jQuery('.designer_app_wrapper').hasClass('admin');
      if ($scope.inAdminArea) {
        $scope.formKey = jQuery('.designer_app_wrapper').data('form-key');
      }
    });


    $scope.designs = Design.query({form_key: $scope.formKey, inspirations: 1});
    $scope.designs.$promise.then(function(data){});

    $scope.cloneDesign = function(designId) {
      var ajaxUrl = '/jewelrydesigner/api/cloneDesign';

      $document.ready(function(){
        $scope.inAdminArea = jQuery('.designer_app_wrapper').hasClass('admin');
        if ($scope.inAdminArea) {
          ajaxUrl = '/jewelrydesigner/adminhtml_api/cloneDesign';
        }
      });

      jQuery.ajax({
          url: ajaxUrl
        , type: 'POST'
        , dataType: 'json'
        , data: {
              id: designId
            , form_key: $scope.formKey
        }
      }).done(function(data){
        if (data.error === false) {
          // 'redirect' user to the review step of their newly cloned design
          $state.go('ui.review', { designId: data.design_id });
        }
        PAN.DesignerWorkspace.showMessage(data.message);
      });
    };

    $scope.addToWishlist = function(designId) {
      var design = Design.get({
          form_key: $scope.formKey
        , id: designId
        , scope_to_customer: 0
      });

      design.$promise.then(function(data){
        var productsAsJson = data.configuration;
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
      });
    };

    $scope.addToCart = function(designId) {
      var design = Design.get({
          form_key: $scope.formKey
        , id: designId
        , scope_to_customer: 0
      });

      design.$promise.then(function(data){
        var productsAsJson = data.configuration;
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
      });
    };

    $scope.showDesigns = function() {
      var showDesigns = ($scope.designs.length > 0) ? true : false;
      return showDesigns;
    };

    $scope.isAdmin = function() {
      return jQuery('.designer_app_wrapper').hasClass('admin');
    };

    $scope.isGuest = function() {
      return jQuery('.designer_app_wrapper').hasClass('guest');
    };


  }
]);

