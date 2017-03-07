angular.module('yahtzeeApp')
    .service("ajaxApiService", function($http, $location, appParametersService) {
    	thiss = this;

    	// Ces flags servent à éviter de surcharger le client de requêtes AJAX de type refresh.
    	// Si la requête précédente n'est pas terminée, la suivante ne se lancera pas
    	this.refreshPagesFlags = {
    		getGames: true,
    		getGame: true,
    		setRequestAsCompleted: function() {
    			thiss.refreshPagesFlags.getGames = true;
            	thiss.refreshPagesFlags.getGame = true;
    		}
    	};




        this.getGames = function (successFunction, errorFunction) {
        	// Check si la précédente requête s'est terminée. Si non, quitte la fonction
        	if (! this.refreshPagesFlags.getGames) {
        		return;
        	}

        	// Indique que la requête est en cours
        	this.refreshPagesFlags.getGames = false;

            this.executeRestApi(appParametersService.paths.api + "games/get",
            	function successCallback(response) {
	            	successFunction(response);
	            	thiss.refreshPagesFlags.setRequestAsCompleted();
	            }, function errorCallback(response) {
	            	errorFunction(response);
	            	thiss.refreshPagesFlags.setRequestAsCompleted();
	            });
        };

        this.createGame = function(datas, successFunction, errorFunction) {
            this.executePostForm(appParametersService.paths.api + "games/create", datas, successFunction, errorFunction);
        };

        this.registerToGame = function(datas, successFunction, errorFunction) {
            this.executePostForm(appParametersService.paths.api + "games/register", datas, successFunction, errorFunction);
        };

        this.beginGame = function(datas, successFunction, errorFunction) {
        	this.executePostForm(appParametersService.paths.api + "games/begin", datas, successFunction, errorFunction);
        };

        this.getGame = function(gameId, successFunction, errorFunction) {
        	// Check si la précédente requête s'est terminée. Si non, quitte la fonction
        	if (! this.refreshPagesFlags.getGame) {
        		return;
        	}

        	// Indique que la requête est en cours
        	this.refreshPagesFlags.getGame = false;

            this.executeRestApi(appParametersService.paths.api + "game/get/" + gameId,
            	function successCallback(response) {
            		successFunction(response);
	            	thiss.refreshPagesFlags.setRequestAsCompleted();
	            }, function errorCallback(response) {
	            	errorFunction(response);
	            	thiss.refreshPagesFlags.setRequestAsCompleted();
	            });
        };

        this.selectCombination = function(gameId, userId, type, value, successFunction, errorFunction) {
            this.executeRestApi(appParametersService.paths.api + "game/select-combination/" + gameId + "/" + userId + "/" + type + "/" + value,
            	successFunction,
            	function errorCallBack(response){
            		errorFunction(response);
            	});
        };

        this.finishGame = function(gameId, successFunction, errorFunction) {
            this.executeRestApi(appParametersService.paths.api + "game/finish-game/" + gameId,
            	successFunction, errorFunction);
        };




        

        // Permet d'exéuter une requête pour une API et qu'en cas de non autorisation, redirige sur la page de connexion
        this.executeRestApi = function(url, successFunction, errorFunction) {
            $http({
                method: "GET",
                url: url
            }).then(function successCallback(response) {
            	successFunction(response);

            	$("#connection-info").css("visibility", "hidden");	// Si la requête a abouti correctement, masque le message d'erreur lié à la connexion
            }, function errorCallback(response) {
            	errorFunction();

                var responseCode = response.status;

                if (responseCode == 401) {              // Si on n'a pas le droit d'exécuter cette requête
                    $location.path("/connection");
                }

                // Si la connexion est perdue, affiche le message d'erreur lié à la connexion
                if (responseCode == -1) {
                	$("#connection-info").css("visibility", "visible");
                }
            });
        };

        // Permet d'exéuter une requête post de formulaire
        this.executePostForm = function(url, datas, successFunction, errorFunction) {
            $http({
                method  : 'POST',
                url     : url,
                data    : $.param(datas),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(successFunction, function errorCallback(response) {
                var responseCode = response.status;

                if (responseCode == 401) {              // Si on n'a pas le droit d'exécuter cette requête
                    $location.path("/connection");
                } else {
                    errorFunction(response);
                }
            });
        };
});