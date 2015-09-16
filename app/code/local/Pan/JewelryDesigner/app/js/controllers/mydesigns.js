/* jshint laxcomma: true */

'use strict';

/**
 * Inspiration Bracelets Controller
 */
angular.module('jewelryDesigner').controller('MyDesignsCtrl', ['$scope', '$stateParams', '$rootScope', '$document', 'Design',
  function($scope, $stateParams, $rootScope, $document, Design) {

    angular.element('.main-container').removeClass('hero-splash');

    $document.ready(function(){
      if (!angular.element('.loader').hasClass('hidden')) {
        angular.element('.loader').addClass('hidden');
      }
    });

    $scope.title = 'My Bracelets';
    $scope.inAdminArea = false;

    $scope.designs = Design.query();
    $scope.designs.$promise.then(function(data){});

    $scope.deleteDesign = function(designId) {
      if (confirm('Are you sure you want to delete the design? This cannot be undone.') === true) {
        var ajaxUrl = '/jewelrydesigner/api/deleteDesign';
        var formKey = null;

        $document.ready(function(){
          $scope.inAdminArea = jQuery('.designer_app_wrapper').hasClass('admin');
          if ($scope.inAdminArea) {
            ajaxUrl = '/jewelrydesigner/adminhtml_api/deleteDesign';
            formKey = jQuery('.designer_app_wrapper').data('form-key');
          }
        });

        jQuery.ajax({
            url: ajaxUrl
          , type: 'POST'
          , dataType: 'json'
          , data: {
                id: designId
              , form_key: formKey
          }
        }).done(function(data){
          if (!data.error) {
            jQuery('li#design_' + data.design_id).fadeOut(700).remove();
          }
          PAN.DesignerWorkspace.showMessage(data.message);
        });
      }
    };

    $scope.showDesigns = function() {
      var showDesigns = ($scope.designs.length > 0) ? true : false;
      return showDesigns;
    };

    $scope.isAdmin = function() {
      return jQuery('.designer_app_wrapper').hasClass('admin');
    };
  }
]);

