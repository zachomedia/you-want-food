/*
    Copyright (c) 2014 Zachary Seguin

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

'use strict';

angular
    .module('YouWantFood')
    .controller('OutletDetails', ['$scope', '$routeParams', 'YouWantFoodAPI', function($scope, $routeParams, YouWantFoodAPI) {
        var outlets = YouWantFoodAPI.outlets();
        $scope.review = {
            name: "",
            email: "",
            review: ""
        };

        $scope.scrollTo = function(anchor) {
            var destination = document.getElementById(anchor);
            destination.scrollIntoView(true);
        }

        $scope.postReview = function() {
            YouWantFoodAPI.addOutletReview($scope.outlet.outlet_id).post($scope.review, function(response) {
                $scope.reviews = YouWantFoodAPI.outletReviews($scope.outlet.outlet_id).get();
                $scope.review = {};
            }, function(error) {
                $scope.review.error = error.data.error || "Sorry, an unexpected error occurred.";
            });
        }

        $scope.today = new Date();
        $scope.isToday = function(weekday) {
            return $scope.today.getDay() == weekday;
        }

        YouWantFoodAPI.outlet($routeParams.outletId).then(function(outlet) {
            $scope.outlet = outlet;

            // Load the menu
            YouWantFoodAPI.menu($scope.outlet.outlet_id).get(function(menu) {
               $scope.menu = menu.menu;
            });

            // Load reviews
            $scope.reviews = YouWantFoodAPI.outletReviews($scope.outlet.outlet_id).get();

            // Load inspections
            $scope.inspections = YouWantFoodAPI.inspections($scope.outlet.outlet_id).get();
        }, function(reason) {
            console.log(reason);
        });
    }]);
