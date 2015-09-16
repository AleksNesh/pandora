'use strict';

/**
 * Application's landing page
 */
angular.module('jewelryDesigner').controller('HomeCtrl', ['$scope', '$stateParams', '$rootScope', '$document',
  function($scope, $stateParams, $rootScope, $document) {
    $scope.title    = 'Jewelry Designer';
    $scope.designId = 'new';

    angular.element('.main-container').addClass('hero-splash');

    $document.ready(function(){
      if (!angular.element('.loader').hasClass('hidden')) {
        angular.element('.loader').addClass('hidden');
      }
    });
  }
]);
