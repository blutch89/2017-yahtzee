angular.module('yahtzeeApp')
    .controller('ConnectionController', function ($scope, $http, $location, authentificationService, userService, appParametersService) {
        // Suppression de tous les timers
        appParametersService.intervals.clearAllPageIntervals();

        $scope.loginFormData = {};
        $scope.registrationFormData = {};
        $scope.loginErrorMsg = "";
        $scope.registrationErrorMsg = "";

        $scope.processLoginForm = function() {
            authentificationService.loginRequest($scope.loginFormData, function successCalllback(response) {
                if (response.data.success == true) {
                    userService.saveUser(response.data["user-id"], response.data["username"]);

                    $location.path("/");
                } else {
                    $scope.loginErrorMsg = "L'utilisateur ou le mot de passe est incorrect.";
                }
            }, function errorCallback(response) {
                $scope.loginErrorMsg = "L'utilisateur ou le mot de passe est incorrect.";
            });
        };

        $scope.processRegistrationForm = function() {
            authentificationService.registerRequest($scope.registrationFormData, function successCalllback(response) {
                if (response.data.success == true) {        // Si réussi à s'enregistrer, log l'utilisateur
                    var loginDatas = {};
                    loginDatas._username = $scope.registrationFormData.username;
                    loginDatas._password = $scope.registrationFormData.first;

                    authentificationService.loginRequest(loginDatas, function(response) {
                        if (response.data.success == true) {
                            userService.saveUser(response.data["user-id"], response.data["username"]);

                            $location.path("/");
                        } else {
                            $scope.registrationErrorMsg = "Impossible de s'identifier. Essayez depuis la zone de connexion.";
                        }
                    }, null);
                } else {
                    $scope.registrationErrorMsg = response.data.error;
                }
            }, function errorCallback(response) {
                $scope.registrationErrorMsg = response.data.error;
            });
        };
    });