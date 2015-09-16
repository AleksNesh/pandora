'use strict';

angular.module('jewelryDesigner')
  .filter('replace', function(){
    return function(input, replaceMe, replaceWith) {
      input       = input       || '';
      replaceMe   = replaceMe   || 'PANDORA';
      replaceWith = replaceWith || '';

      var out = input.replace(replaceMe, replaceWith);
      return out;
    };
});
