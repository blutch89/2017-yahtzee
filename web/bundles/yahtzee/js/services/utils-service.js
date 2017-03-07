angular.module('yahtzeeApp')
    .service("utilsService", function() {
        this.random = function random(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        };
});