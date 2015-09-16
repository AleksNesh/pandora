'use strict';

angular.module('jewelryDesigner').controller('ShareSidebarCtrl', ['$scope', '$stateParams', 'design',
  function($scope, $stateParams, design) {
    $scope.designStep   = 'share';
    $scope.line_items   = (design !== null && design !== undefined) ? JSON.parse(design.configuration) : [];
    $scope.total_price  = 0;


    angular.forEach($scope.line_items, function(item){
      $scope.total_price = $scope.total_price + (item.base_price * (item.qty + item.quantity_owned));
    });
  }
]);
