angular.module('yahtzeeApp')
    .service("authentificationService", function($http, $location, appParametersService) {
        this.loginRequest = function (datas, successFunction, errorFunction) {
            this.executePostForm(appParametersService.paths.prefix + "login_check", datas, successFunction, errorFunction);
        };

        this.registerRequest = function (datas, successFunction, errorFunction) {
            this.executePostForm(appParametersService.paths.prefix + "register", datas, successFunction, errorFunction);
        };

        this.logoutRequest = function(successFunction, errorFunction) {
        	this.executeRestApi(appParametersService.paths.prefix + "logout", successFunction, errorFunction);
        };


        
        // Permet d'exécuter une requête de type GET
        this.executeRestApi = function(url, successFunction, errorFunction) {
            $http({
                method: "GET",
                url: url
            }).then(successFunction, errorFunction);
        };

        // Permet d'exéuter une requête post de formulaire
        this.executePostForm = function(url, datas, successFunction, errorFunction) {
            $http({
                method  : 'POST',
                url     : url,
                data    : $.param(datas),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(successFunction, errorFunction);
        };
});