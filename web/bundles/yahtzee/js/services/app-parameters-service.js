angular.module('yahtzeeApp')
    .service("appParametersService", function() {
        var thiss = this;

        // Paths
        var paths = {};
        paths.prefix = "";
        paths.webResources = "bundles/yahtzee/";
        paths.imagesResources = paths.webResources + "images/";
        paths.api = paths.prefix + "frontend-api/";

        this.paths = paths;

        // Intervals
        var intervals = {};
        intervals.refreshGamesPageInterval = 0;
        intervals.refreshGamePageInterval = 0;
        intervals.clearAllPageIntervals = function() {
            clearInterval(intervals.refreshGamesPageInterval);
            clearInterval(intervals.refreshGamePageInterval);
        };
        
        this.intervals = intervals;
});