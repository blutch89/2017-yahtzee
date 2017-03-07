angular.module('yahtzeeApp')
    .service("gamesUtilsService", function() {
        this.checkIfGameContainUser = function(game, userId) {
            for (player in game.players) {
                if (game.players[player]["id"] == userId) {
                    return true;
                }
            }

            return false;
        };
});