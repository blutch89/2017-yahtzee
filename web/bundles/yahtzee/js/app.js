jQuery.fn.rotate = function(angle) {
    // caching the object for performance reasons
    var $elem = $(this);

    // we use a pseudo object for the animation
    // (starts from `0` to `angle`), you can name it as you want
    $({deg: 0}).animate({deg: angle}, {
        duration: 1000,
        step: function(now) {
            // in the step-callback (that is fired each step of the animation),
            // you can use the `now` paramter which contains the current
            // animation-position (`0` up to `angle`)
            $elem.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    });
};

angular
    .module('yahtzeeApp', ['ngRoute', 'ui.bootstrap'])
    .config(function ($routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'bundles/yahtzee/views/index.html',
                controller: 'IndexController'
            })
            .when('/game/:gameId', {
                templateUrl: 'bundles/yahtzee/views/game.html',
                controller: 'GameController'
            })
            .when('/connection', {
                templateUrl: 'bundles/yahtzee/views/connection.html',
                controller: 'ConnectionController'
            })
            .otherwise({
                redirectTo: '/'
            });
    });