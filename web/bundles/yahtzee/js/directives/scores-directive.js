angular.module('yahtzeeApp')
    .directive('scores', ["appParametersService", function (appParametersService) {
        var directiveDefinition = {
            restrict: "E",
            replace: true,
            scope: true,
            link: function($scope, $elem, $attrs) {

            },
            templateUrl: appParametersService.paths.webResources + "js/directives/scores-html.html"
        };

        return directiveDefinition;
    }]);