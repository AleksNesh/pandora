/* jshint laxcomma: true */

'use strict';

angular.module('jewelryDesigner').controller('DesignCtrl', ['$scope', '$rootScope', '$stateParams', '$document', '$window', 'Design',
  function($scope, $rootScope, $stateParams, $document, $window, Design) {
    // console.log('hit DesignCtrl');

    angular.element('.main-container').removeClass('hero-splash');

    var win = angular.element($window);
    win.bind('resize', function(){
      PAN.DesignerWorkspace.reloadDesignOnResize();
    });

    $scope.inAdminArea = false;


    $scope.design = Design.get({id: $stateParams.designId});
    $scope.design.$promise.then(function(design){
      if(design.id !== undefined) {
        $scope.design   = design;
        $scope.designId = design.id;

        PAN.DesignerWorkspace.setCurrentDesignId(design.id);
      } else {
        $scope.design   = null;
        $scope.designId = 'new';
      }

      PAN.DesignerWorkspace.startOver(false);
      PAN.DesignerWorkspace.loadDesign($scope.design);


      $document.ready(function(){
        $scope.inAdminArea = jQuery('.designer_app_wrapper').hasClass('admin');
        if ($scope.inAdminArea) {
          if ($scope.design !== null) {
            var inspirationCheckBox = jQuery('#inspiration_chkbox')
              , availableCheckBox   = jQuery('#is_available_chkbox');

            inspirationCheckBox.prop('checked', $scope.design.is_inspiration_design);
            availableCheckBox.prop('checked', $scope.design.is_available);
          }
        }
      });


      // broadcast a message to other controllers that can act on with $rootScope.$on('design_loaded', function(){})
      $rootScope.$emit('design_loaded', design);
    });
  }
]);
