(function () {

    var app = angular.module("pfgaLinks", []);

    var LinksController = function ($scope, $http) {
        var onLinksComplete = function (response) {
            $scope.links = response.data;
        }

        var onError = function (reason) {
            $scope.error = "Couldn't find any links";
        }

        $http.get("links.json")
            .then(onLinksComplete, onError);

    };

    app.controller("LinksController", ["$scope", "$http", LinksController]);
}());