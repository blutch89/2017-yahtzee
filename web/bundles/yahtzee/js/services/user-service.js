angular.module('yahtzeeApp')
    .service("userService", function() {
        this.getUserId = function() {
            return localStorage.getItem("user-id");
        };

        this.getUsername = function() {
            return localStorage.getItem("username");
        };

        this.saveUser = function(userId, username) {
            localStorage.setItem("user-id", userId);
            localStorage.setItem("username", username);
        };
});