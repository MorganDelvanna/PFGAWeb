(function () {

    var app = angular.module("pfgaNews", []);

    var NewsController = function ($scope, $http) {
        var onNewsComplete = function (response) {
            $scope.news = response.data;
        }

        var onError = function (reason) {
            $scope.error = "Couldn't find any news";
            console.log(reason);
        }

        $http.get("news_data.json")
            .then(onNewsComplete, onError);
    };

    app.controller("NewsController", ["$scope", "$http", NewsController]);
    app.filter('to_trusted', ['$sce', function ($sce) {
        return function (text) {
            return $sce.trustAsHtml(text);
        };
    }]);
}());