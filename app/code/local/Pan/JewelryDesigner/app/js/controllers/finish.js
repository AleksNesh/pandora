'use strict';

angular.module('jewelryDesigner').controller('FinishCtrl', ['$scope', '$stateParams',
  function($scope, $stateParams) {
    $scope.designStep = 'finish';
    angular.element('.sidebar-toggle').addClass($scope.designStep);
  }
]);
