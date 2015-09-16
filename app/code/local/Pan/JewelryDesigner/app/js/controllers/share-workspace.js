'use strict';

angular.module('jewelryDesigner').controller('ShareWorkspaceCtrl', ['$scope', '$stateParams', '$rootScope', 'design',
  function($scope, $stateParams, $rootScope, design) {
    $scope.design = design;
    $rootScope.$emit('shared_design_loaded', design);
  }
]);
