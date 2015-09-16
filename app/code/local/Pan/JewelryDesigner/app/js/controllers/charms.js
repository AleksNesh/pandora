'use strict';

angular.module('jewelryDesigner').controller('CharmsCtrl', ['$scope', '$stateParams', '$document', 'products',
  function($scope, $stateParams, $document, products) {
    $scope.designStep = 'charms';

    // filters for items
    $scope.colors     = [];
    $scope.themes     = [];
    $scope.materials  = [];

    $scope.products = products;


    angular.element('.sidebar-toggle').addClass($scope.designStep)
      .removeClass('review')
      .removeClass('bracelets')
      .removeClass('clips')
      .removeClass('active');

    $document.ready(function(){
      if (!angular.element('.loader').hasClass('hidden')) {
        angular.element('.loader').addClass('hidden');
      }
    });


    angular.forEach($scope.products, function(item){
       // Populate the color filters
      if (item.color instanceof Array) {
        angular.forEach(item.color, function(c){
          if (item.color !== null && $scope.colors.indexOf(c) == -1 ) {
            $scope.colors.push(c);
          }
        });
      } else {
        if (item.color !== null && $scope.colors.indexOf(item.color) == -1 ) {
          $scope.colors.push(item.color);
        }
      }

      // Populate the theme filters
      if (item.theme instanceof Array) {
        angular.forEach(item.theme, function(t){
          if (item.theme !== null && $scope.themes.indexOf(t) == -1 ) {
            $scope.themes.push(t);
          }
        });
      } else {
        if (item.theme !== null && $scope.themes.indexOf(item.theme) == -1 ) {
          $scope.themes.push(item.theme);
        }
      }

      // Populate the material filters
      if (item.material instanceof Array) {
        angular.forEach(item.material, function(m){
          if (item.material !== null && $scope.materials.indexOf(m) == -1 ) {
            $scope.materials.push(m);
          }
        });
      } else {
        if (item.material !== null && $scope.materials.indexOf(item.material) == -1 ) {
          $scope.materials.push(item.material);
        }
      }
    });
  }
]);
