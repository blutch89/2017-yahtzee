angular.module('yahtzeeApp')
    .controller('MainController', function ($scope, $location, authentificationService, appParametersService) {
    	$scope.logout = function() {
    		authentificationService.logoutRequest(function successCallback(response) {
    			if (response.data.success == true) {
	    			$location.path("/connection");
	    		} else {

	    		}
    		}, function errorCallback(response) {
    			
    		});
    	};
    });