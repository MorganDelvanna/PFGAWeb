(function () {

    var app = angular.module("pfgaNews", []);

    var NewsController = function ($scope, $http) {
        var onNewsComplete = function (response) {
            $scope.news = response.data;
        }

        var onError = function (reason) {
            $scope.error = "Couldn't find any links";
        }

        $http.get("news.json")
            .then(onNewsComplete, onError);
    };

    app.controller("NewsController", ["$scope", "$http", NewsController]);
}());