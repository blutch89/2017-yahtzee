angular.module('yahtzeeApp')
    .controller('GameController', function ($scope, $routeParams, ajaxApiService, userService, utilsService, appParametersService) {
        var thiss = this;

        // Suppression de tous les timers
        appParametersService.intervals.clearAllPageIntervals();

        $scope.game = {};
        $scope.players = {};
        $scope.playerTurn = {};
        $scope.isMyTurn = false;
        $scope.myTurnText = undefined;

        // Alerts
        $scope.alertMessage = "";
        $scope.isAlertClosed = true;

        // Fonction retournant si oui ou non il faut désactiver le bouton de lancé de dés
        $scope.rollDicesButton = function() {
            // Si ce n'est pas mon tour
            if (! $scope.isMyTurn) {
                return true;
            }

            // Si c'est le dernier tour
            if ($scope.rollNo >= 3) {
                return true;
            }

            // Si la partie est terminée
            if ($scope.game["is-ended"]) {
            	return true;
            }

            return false;
        };

        // Click sur le bouton "Terminer la partie"
        $scope.finishGameButton = function() {
        	if ($scope.game["is-ended"] == true) {
        		return;
        	}

        	if (confirm("Voulez-vous vraiment quitter cette partie ?")) {
        		ajaxApiService.finishGame($routeParams.gameId,
	            	function successFunction(response) {
		                if (response.data.success == true) {
		                    
		                } else {
		                	$scope.createAlert(response.data.error);
		                }
	            	}, function errorFunction(response) {
	            		$scope.createAlert("Impossible de terminer la partie");
	            	});
        	}
        };


        $scope.rollNo = 0;
        $scope.dicesAnimationsIntervals = {};
        $scope.dices = {};
        $scope.combinations = {};
        $scope.entries = {
        	first: {"ones": "Un", "twos": "Deux", "threes": "Trois", "fours": "Quatre", "fives": "Cinq", "sixes": "Six"},
        	second: {"one-pair": "Une paire", "two-pairs": "Deux paires", "three-of-a-kind": "Brelan", "four-of-a-kind": "Carré", "full-house": "Full", "small-straight": "Petite suite", "large-straight": "Grande suite", "chance": "Chance", "yahtzee": "Yahtzee"}
        }
        this.rollDicesFlag = false;
        

        $scope.dicesUtils = {
            // Retourne les tailles des zones de dés pour le calcul de la nouvelle position des dés
            getDicesDimensions: function() {
                var dicesArea = $("#dices-area");
                var diceSize = $("#" + $scope.dices[0].id).width();
                
                var widthArea = dicesArea.width();
                var widthPerDice = widthArea / 5;
                var heightArea = dicesArea.height() - diceSize;        // TODO Pas forcément besoin d'un service pour la taille du dé. Trouver un moyen de l'inclure autrement

                return {
                    widthPerDice: widthPerDice,
                    heightArea: heightArea
                };
            },
            // Positionne les dés au centre de la zone
            putDicesOnCenter: function() {
                $("#dices-area").css({display: "flex"});
                $("#dices-area img").css({position: "inherit"});
            },
            // Retourne les valeurs de dés affichés
            getDicesValues: function() {
                var values = new Array();

                for (index in $scope.dices) {
                    var dice = $scope.dices[index];

                    values.push(dice.value);
                }

                return values;
            }
        };

        $scope.combinationsUtils = {
            // Trouve les différentes combinaisons
            findCombinations: function() {
                $scope.$apply(function() {
                    var values = $scope.dicesUtils.getDicesValues();

                    $scope.combinations.ones = $scope.combinationsUtils.findSamesValues(values, 1);
                    $scope.combinations.twos = $scope.combinationsUtils.findSamesValues(values, 2);
                    $scope.combinations.threes = $scope.combinationsUtils.findSamesValues(values, 3);
                    $scope.combinations.fours = $scope.combinationsUtils.findSamesValues(values, 4);
                    $scope.combinations.fives = $scope.combinationsUtils.findSamesValues(values, 5);
                    $scope.combinations.sixes = $scope.combinationsUtils.findSamesValues(values, 6);
                    $scope.combinations["one-pair"] = $scope.combinationsUtils.findMultiples(values, 2, false);
                    $scope.combinations["two-pairs"] = $scope.combinationsUtils.findMultiples(values, 2, true);
                    $scope.combinations["three-of-a-kind"] = $scope.combinationsUtils.findMultiples(values, 3, false);
                    $scope.combinations["four-of-a-kind"] = $scope.combinationsUtils.findMultiples(values, 4, false);
                    $scope.combinations["full-house"] = $scope.combinationsUtils.findFullHouse(values);
                    $scope.combinations["small-straight"] = $scope.combinationsUtils.findStraight(values, 4);
                    $scope.combinations["large-straight"] = $scope.combinationsUtils.findStraight(values, 5);
                    $scope.combinations["chance"] = $scope.combinationsUtils.getChanceResult(values);
                    $scope.combinations["yahtzee"] = $scope.combinationsUtils.findMultiples(values, 5, false);
                });
            },
            // Fonction pour les valeurs de 1 à 6
            findSamesValues: function(arr, value) {
                var nbOccurences = $.grep(arr, function (elem) {
                    return elem === value;
                }).length;

                return nbOccurences * value;
            },
            // Fonction cherchant les multiples : pair, double pair, brelan, carré et yahtzee
            findMultiples: function(arr, nbOccurence, doublePair) {
                var pairArr = new Array();

                // Cherche les multiples
                for (var i = 1; i <= 6; i++) {
                    var occurences = $.grep(arr, function (elem) {
                        return elem === i;
                    });

                    if (occurences.length >= nbOccurence) {
                        if (occurences.length >= nbOccurence * 2) {     // Permet de gérer les double pair
                            pairArr.push(nbOccurence * i);
                            pairArr.push(nbOccurence * i);
                        } else {
                            pairArr.push(nbOccurence * i);
                        }
                    }
                }

                // Si aucune combinaison trouvée, retourne 0
                if (pairArr.length == 0) {
                    return 0;
                }

                pairArr.sort(function(a, b){return a - b;});

                // Si besoin de trouver une pair
                if (nbOccurence == 2) {
                    if (doublePair) {                       // Si besoin de trouver une double pair
                        if (pairArr.length < 2) {return 0;}

                        return pairArr[pairArr.length - 1] + pairArr[pairArr.length - 2];
                    }

                    return pairArr[pairArr.length - 1];
                } else if (nbOccurence == 5) {
                    return 50;
                } else {                                    // Si besoin de trouver autre chose qu'une pair ou double pair
                    var toReturn = 0;

                    for (index in arr) {
                        toReturn += arr[index];
                    }

                    return toReturn;
                }                
            },
            // Fonction cherchant un full
            findFullHouse: function(arr) {
                var multiple3 = false;
                var multiple2 = false;

                for (var i = 1; i <= 6; i++) {
                    var multiple = $.grep(arr, function (elem) {
                        return elem === i;
                    }).length;

                    if (multiple >= 3) {
                        multiple3 = true;
                    } else if (multiple == 2) {
                        multiple2 = true;
                    }
                }

                return (multiple3 && multiple2) ? 25 : 0;
            },
            // Fonction cherchant des suites
            findStraight: function(arr, straightLength) {
                var consecutive = 1;
                var smallStraight = false;
                var largeStraight = false;

                arr.sort();

                for (var i = 0; i < arr.length; i++) {
                    if (i == arr.length - 1) {
                        break;
                    }

                    // Si le chiffre suivant est la suite du chiffre actuel
                    if (arr[i + 1] == arr[i] + 1) {
                        consecutive++;
                    } else if (arr[i + 1] == arr[i]) {

                    } else {
                        consecutive = 1;
                    }

                    if (consecutive >= 4) {
                        smallStraight = true;
                    }

                    if (consecutive >= 5) {
                        largeStraight = true;
                    }
                }

                if (straightLength == 4) {
                    return (smallStraight) ? 30 : 0;
                } else if (straightLength == 5) {
                    return (largeStraight) ? 40 : 0;
                }
            },
            // Fonction retournant le résultat de la chance
            getChanceResult: function(arr) {
                var toReturn = 0;

                for (index in arr) {
                    toReturn += arr[index];
                }

                return toReturn;
            },
        };

        // Retourne le lien de l'image d'un dé dice
        $scope.getImageLink = function(value) {
            return appParametersService.paths.imagesResources + "dices/" + value + ".png";
        };

        // Inscrit le résultat dans une cellule de score. Inscrit soit le score reçu par Ajax, soit une combinaison
        $scope.getScore = function(index, type) {
            // Si ce n'est pas moi
            if ($scope.players[index]["id"] != userService.getUserId()) {
                return $scope.players[index]["score"][type];
            }

            // Si un score a déjà été inscrit
            if ($scope.players[index]["score"][type] != null) {
                return $scope.players[index]["score"][type];
            }

            return $scope.combinations[type];
        };

        // Test si oui ou non la cellule de score est une combinaison
        $scope.getScoreClass = function(index, type) {
            if ($scope.players[index]["id"] == userService.getUserId()
                && $scope.players[index]["score"][type] == null
                && $scope.combinations[type] != null) {
                return true;
            } else {
                return false;
            }
        };

        // Sélectionne un dé
        $scope.selectDice = function(dice) {
        	if ($scope.rollNo == 0) {
                return;
            }

            dice["selected"] = ! dice["selected"];
        };

        // Lance les dés
        $scope.rollDices = function() {
        	// Gère le flag: permet d'éviter un bug lorsque l'on click 2x fois sur le bouton
        	if (thiss.rollDicesFlag) {
        		return;
        	}

        	thiss.rollDicesFlag = true;

            // Enlève le centrage
            $("#dices-area").css({display: "block"});
            $("#dices-area img").css({position: "absolute"});

            var diceDimensions = $scope.dicesUtils.getDicesDimensions();
            var diceSize = $("#" + $scope.dices[0]["id"]).width();

            for (index in $scope.dices) {
            	// Continue la boucle for si le dé est sélectionné
            	if ($scope.dices[index]["selected"]) {
            		continue;
            	}

                var dice = $("#d" + index);

                // Détermine la nouvelle position du dé en cours
                var xFrom = diceDimensions.widthPerDice * index;
                var xTo = xFrom + diceDimensions.widthPerDice - diceSize;
                var randomPositionX = utilsService.random(xFrom, xTo);
                var randomPositionY = utilsService.random(1, diceDimensions.heightArea);

                // Anime le dé en cours
                dice.animate({left: randomPositionX + 15 + "px", top: randomPositionY + "px"}, 1000);
                dice.rotate(360);

                $scope.dicesAnimationsIntervals[index] = setInterval(function(dice, index) {
                    var newValue = utilsService.random(1, 6);
                    dice.attr("src", $scope.getImageLink(newValue));
                    $scope.dices[index].value = newValue;
                }, 200, dice, index);
            }

            // Supprime les Intervals d'animation des dés
            setTimeout(function() {
                for (inter in $scope.dicesAnimationsIntervals) {
                    clearInterval($scope.dicesAnimationsIntervals[inter]);
                }

                // Incrémente le rollNo
                $scope.rollNo++;

                // Sauvegarde la partie
                $scope.saveLocalDatas();

                // Affiche les combinaisons possibles
                $scope.combinationsUtils.findCombinations();

                // Indique que l'animation est terminée
                thiss.rollDicesFlag = false;
            }, 1000);
        };

        $scope.selectCombination = function(index, type) {
            // Si ce n'est pas moi, annule le click
            if ($scope.players[index]["id"] != userService.getUserId()) {
                return;
            }

            // Si score déjà inscrit, annule le click
            if ($scope.players[index]["score"][type] != null) {
                return;
            }

            // Si ce n'est pas à mon tour
            if (! $scope.isMyTurn) {
            	return;
            }

            // Si aucun lancé n'a été effectué
            if ($scope.rollNo == 0) {
            	return;
            }

            ajaxApiService.selectCombination($routeParams.gameId, userService.getUserId(), type, $scope.combinations[type],
            	function successFunction(response) {
	                if (response.data.success == true) {
	                    $scope.resetValues();
	                    $scope.saveLocalDatas();
	                } else {
	                	$scope.createAlert("Une erreur s'est produite lors de l'inscription du résultat.");
	                }
            	}, function errorFunction(response) {
            		$scope.createAlert("Une erreur s'est produite lors de l'inscription du résultat.");
            	});
        };

        $scope.resetValues = function() {
            $scope.rollNo = 0;

            $scope.dices = {
                0: {
                    id: "d0",
                    value: 1,
                    selected: false
                },
                1: {
                    id: "d1",
                    value: 1,
                    selected: false
                },
                2: {
                    id: "d2",
                    value: 1,
                    selected: false
                },
                3: {
                    id: "d3",
                    value: 1,
                    selected: false
                },
                4: {
                    id: "d4",
                    value: 1,
                    selected: false
                }
            };

            $scope.combinations = {};
        };

        $scope.loadLocalDatas = function() {
        	if (! localStorage.getItem("game" + $routeParams.gameId)) {
                return;
            }

            var gameDatas = JSON.parse(localStorage.getItem("game" + $routeParams.gameId));
			$scope.rollNo = gameDatas["rollNo"];
            $scope.dices = gameDatas["dices"];
        };


        $scope.saveLocalDatas = function() {
        	var gameDatas = {
        		"rollNo": $scope.rollNo,
        		"dices": $scope.dices
        	};

        	localStorage.setItem("game" + $routeParams.gameId, JSON.stringify(gameDatas));
        };

        // Crée un message d'alerte
        $scope.createAlert = function(message) {
        	$scope.alertMessage = message;
        	$scope.isAlertClosed = false;
        };

        // Ferme le message d'erreur sous forme d'alerte
        $scope.closeAlertMessage = function() {
        	$scope.alertMessage = "";
        	$scope.isAlertClosed = true;
        };

        // Recharge les parties
        $scope.refreshPage = function() {
            var gameId = $routeParams.gameId;

            ajaxApiService.getGame(gameId, function successFunction(response) {
                if (response.data.success == true) {
                	$scope.game = response.data;
                    $scope.players = response.data["players"];
                    $scope.playerTurn = response.data["player-turn"];

                    if (response.data["is-ended"] == false) {   // Si la partie n'est pas terminée
                        if (response.data["player-turn"]["id"] == userService.getUserId()) {    // Si c'est à moi de jouer
                            $scope.isMyTurn = true;
                        	$scope.myTurnText = "C'est votre tour";
                        } else {                                                                // Si ce n'est pas à moi de jouer
                            $scope.isMyTurn = false;
                            $scope.myTurnText = "Attendez votre tour";
                        }
                    } else {    // Si la partie est terminée
                    	// Définit qui est le gagnant
                    	if (response.data["winner"]) {
	                    	var winnerId = response.data["winner"]["id"];

	                    	for (var el in $scope.players) {
	                    		var element = $scope.players[el];

	                    		if (element["id"] == winnerId) {	// Si l'élément en cours est le gagnant
	                    			element["winner"] = true;
	                    		} else {							// Si l'élément en cours n'est pas le gagnant
	                    			element["winner"] = false;
	                    		}
	                    	};
	                    }

                    	// Supprime le localStorage s'il existe
                    	if (localStorage.getItem("game" + $routeParams.gameId)) {
                    		localStorage.removeItem("game" + $routeParams.gameId);
                    	}
                    }
                } else {
                	$scope.createAlert("Une erreur s'est produite. Impossible d'afficher a partie.");
                }
            }, function errorFunction(response) {
            	
            });
        };

        $scope.resetValues();
        $scope.loadLocalDatas();
        $scope.refreshPage();
        appParametersService.intervals.refreshGamePageInterval = setInterval($scope.refreshPage, 2000);
    });