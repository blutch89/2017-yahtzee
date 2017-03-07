angular.module('yahtzeeApp')
    .directive('game', ["appParametersService", function (appParametersService) {
        var directiveDefinition = {
            restrict: "E",
            replace: true,
            scope: true,
            link: function($scope, $elem, $attrs) {
                $scope.gamesType = $attrs.gamesType;        // Stock le type de jeu (inProgress, nonStarted ou closed)
            },
            templateUrl: appParametersService.paths.webResources + "js/directives/game-directive-html.html"
        };

        return directiveDefinition;
    }]);