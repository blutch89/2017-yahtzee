angular.module('yahtzeeApp')
    .controller('IndexController', function ($scope, $location, ajaxApiService, userService, gamesUtilsService, appParametersService) {
    	indexController = this;

        // Suppression de tous les timers
        appParametersService.intervals.clearAllPageIntervals();

        // Section parties
        $scope.games = {};
        $scope.inProgress = {};
        $scope.nonStarted = {};
        $scope.closed = {};

        // Alerts
        $scope.alertMessage = "";
        $scope.isAlertClosed = true;

        // Section création de partie
        $scope.isCreateCollapsed = true;
        $scope.createGameData = {};

        // Création d'une partie
        $scope.createGame = function() {
            $scope.createGameData["user-id"] = userService.getUserId();

            ajaxApiService.createGame($scope.createGameData, function successCallback(response) {
                if (response.data.success == true) {
                    $scope.isCreateCollapsed = true;
                } else {
                	indexController.createAlert(response.data.error);
                }
            }, function errorCallback(response) {
                indexController.createAlert(response.data.error);
            });
        };

        $scope.openGame = function(game) {
            $location.path("/game/" + game.id);
        };

        $scope.beginGame = function(game) {
        	var datas = {
                "game-id": game.id,
                "user-id": userService.getUserId()
            };

        	ajaxApiService.beginGame(datas, function successCallback(response) {
        		if (response.data.success == true) {
					$location.path("/game/" + game.id);
        		} else {
        			indexController.createAlert(response.data.error);
        		}
        	}, function errorCallback(response) {
        		indexController.createAlert(response.data.error);
        	});
        };

        // Inscription d'une partie
        $scope.registerToGame = function(game) {
            var datas = {
                "game-id": game.id,
                "user-id": userService.getUserId()
            };

            ajaxApiService.registerToGame(datas, function successCallback(response) {
                if (response.data.success == true) {
                    game.buttonDisabled = true;
                } else {
                    indexController.createAlert(response.data.error);
                }
            }, function errorCallback(response) {
                indexController.createAlert(response.data.error);
            });
        };

        // Crée un message d'alerte
        this.createAlert = function(message) {
        	$scope.alertMessage = message;
        	$scope.isAlertClosed = false;
        };

        // Ferme le message d'erreur sous forme d'alerte
        $scope.closeAlertMessage = function() {
        	$scope.alertMessage = "";
        	$scope.isAlertClosed = true;
        };

        // Recharge les parties
        this.refreshPage = function() {
            ajaxApiService.getGames(function successCallback(response) {
                // Rajoute les attributs "isMyTurn" et "myTurnText" pour les parties en cours (status 1)
                for (var i in response.data.inProgress) {
                    var currentGame = response.data.inProgress[i];

                    if (currentGame != null) {
                        if (currentGame.player_turn == userService.getUserId()) {
                            currentGame.isMyTurn = true;
                            currentGame.myTurnText = "C'est votre tour";
                        } else {
                            currentGame.isMyTurn = false;
                            currentGame.myTurnText = "Attendez votre tour";
                        }
                    }
                }

                // Rajoute les attributs du texte du bouton d'action ainsi que leur class pour toutes les parties
                for (var i in response.data) {
                    for (var ii in response.data[i]) {
                        var currentGame = response.data[i][ii];

                        if (currentGame.status == 0) {                  // Si partie non commencée
                            currentGame.panelClass = "panel-primary";

                            if (currentGame.owner == userService.getUserId()) {
                                currentGame.buttonType = 0;
                            } else {
                                currentGame.buttonType = 1;

                                if (gamesUtilsService.checkIfGameContainUser(currentGame, userService.getUserId())) {
                                    currentGame.buttonRegisterText = "Déjà inscrit";
                                    currentGame.buttonDisabled = true;
                                } else {
                                    currentGame.buttonRegisterText = "S'inscrire";
                                }
                            }
                        } else if (currentGame.status == 1) {           // Si partie en cours
                            currentGame.panelClass = "panel-success";
                            currentGame.buttonType = 2;
                        } else if (currentGame.status == 2) {           // Si partie terminée
                            currentGame.panelClass = "panel-danger";
                            currentGame.buttonType = 2;
                        }
                    }
                }

                $scope.games = response.data;
                $scope.inProgress = response.data["inProgress"];
                $scope.nonStarted = response.data["nonStarted"];
                $scope.closed = response.data["closed"];
            }, function errorCallback(response) {
            	
            });
        };

        this.refreshPage();
        appParametersService.intervals.refreshGamesPageInterval = setInterval(this.refreshPage, 2000);
    });