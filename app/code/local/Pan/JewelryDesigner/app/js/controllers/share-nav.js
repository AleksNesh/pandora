 'use strict';

angular.module('jewelryDesigner').controller('ShareNavCtrl', ['$scope', '$stateParams', '$rootScope',
  function($scope, $stateParams, $rootScope) {
    $scope.title = 'Share This Bracelet';

    // take action on broadcast message from a $rootScope.$emit('shared_design_loaded');
    $rootScope.$on('shared_design_loaded', function(event, data){
      $scope.title = data.name;
    });
  }
]);
