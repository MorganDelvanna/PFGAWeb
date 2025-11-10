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

    const MenuController = function ($scope, $http) {
        $scope.isOpen = false;
        $scope.menu = [];

        var onMenuComplete = function (response) {
            $scope.menu = response.data;
            // Add dropdown open flag
            $scope.menu.forEach(function (m) {
                m.open = false;
            });
        }

        var onError = function (reason) {
            $scope.error = "Couldn't find the menu data";
        }

        $http.get("menu.json")
            .then(onMenuComplete, onError);

        $scope.toggleMenu = function () {
            $scope.isOpen = !$scope.isOpen;
        };

        $scope.toggleItem = function (m, e) {
            // stop Vue-like bubbling
            e.stopPropagation();

            if (!m.children) return;

            m.open = !m.open;

            $scope.menu.forEach(function (i) {
                if (i !== m) i.open = false;
            });
        };
    }

    app.controller("LinksController", ["$scope", "$http", LinksController]);
    app.controller("MenuController", ["$scope", "$http", MenuController]);
}());