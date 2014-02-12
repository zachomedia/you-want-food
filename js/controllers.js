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

angular
    .module('FoodServices', ['ngRoute', 'ngSanitize'])
    // RouteProvider
    // =============
    // Configure the routing for the application.
    .config(function ($routeProvider) {
        $routeProvider
            .when('/outlet/:outlet_id', {
                // Load Single Location
                templateUrl: 'views/outlet.html',
                controller: 'Outlet'
            }) // End of /location/:id
            .when('/email-subscribe', {
                templateUrl: 'views/email-subscribe.html',
                controller: 'EmailSubscribe'
            })
            .when('/email-unsubscribe', {
                templateUrl: 'views/email-unsubscribe.html',
                controller: 'EmailUnsubscribe'
            })
            .when('/', {
                // Ask To Select Location
                templateUrl: 'views/overview.html',
                controller: 'Overview'
            }); // End of /
    })
    .filter('unsafe', function ($sce) {
        return function(val) {
            return $sce.trustAsHtml(val);
        }
    })
    
    // Locations Controller
    // ====================
    // Main controller for the web application.
    .controller('Outlets', function($scope, $http) {
        $scope.outlets = [];
        
        // Load data from the API
        $http.get('api/?data=outlets')
            .success(function (data) {
                $scope.outlets = data;

                // Load menu information
                $http.get('api/?data=menu').success(function (menu_outlets) {
                    for (var outlet in $scope.outlets) {
                        for (var menu_outlet in menu_outlets) {
                            if (menu_outlets[menu_outlet].outlet_id == $scope.outlets[outlet].outlet_id) {
                               $scope.outlets[outlet].menu = menu_outlets[menu_outlet].menu;
                                
                               for (var index in $scope.outlets[outlet].menu) {
                                    var meals = [];

                                    for (var meal_name in $scope.outlets[outlet].menu[index].meals) {
                                        var meal = {
                                            order: (meal_name == 'lunch') ? 0 : 1,
                                            name: meal_name,
                                            items: $scope.outlets[outlet].menu[index].meals[meal_name]
                                        }

                                        meals.push(meal);
                                    }// End of for

                                    $scope.outlets[outlet].menu[index] = {
                                        date: $scope.outlets[outlet].menu[index].date,
                                        day: $scope.outlets[outlet].menu[index].day,
                                        meals: meals,
                                        notes: $scope.outlets[outlet].menu[index].notes
                                    };
                               }// End of for
                            }// End of if
                        }// End of for
                    }// End of for
                });
            })// End of success
            .error(function (error) {
                console.log(error);
                alert('Failed to load data. Please try again at a later time.');
            });// End of error
    })
    
    // Overview Controller
    // ===================
    // Controller for overview information (no outlet selected).
    .controller('Overview', function ($scope) {})
    
    // Outlet Controller
    // =================
    // Displays information about an outlet.
    .controller('Outlet', function ($scope, $routeParams, $http, $location) {
        $scope.today = new Date();
        $scope.weekdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $scope.outlet = null;

        // Handle selection
        for (var index in $scope.outlets) {
            var outlet = $scope.outlets[index];
            
            outlet.is_selected = outlet.outlet_id == $routeParams.outlet_id;
            
            if (outlet.outlet_id == $routeParams.outlet_id) {
               $scope.outlet = outlet;
            }// End of if
         }// End of for

        if ($scope.outlet == null) {
           $location.path('/');
        }

        // Convert hours into an array, fixing a sorting issue.
        if (!($scope.outlet.opening_hours instanceof Array)) {
            var hours = [];
            
            for (var key in $scope.outlet.opening_hours) {
                var day = {
                    opening_hour : $scope.outlet.opening_hours[key].opening_hour,
                    closing_hour : $scope.outlet.opening_hours[key].closing_hour,
                    weekday : key,
                    order : $scope.weekdays.indexOf(key)
                } // End of day
                
                hours.push(day);
            }// End of foreach
        
            $scope.outlet.opening_hours = hours;
        }// End of if
    })
    // EmailSubscribe Controller
    // =================
    // Handles subscribing to daily emails.
    .controller('EmailSubscribe', function($scope, $http) {
       
        $scope.email = "";
        $scope.error = "";
        $scope.success = "";

        $scope.subscribe = function() {
            if ($scope.email == "" || $scope.email == undefined) {
                $scope.error = "Please enter your email address.";
                $scope.success = "";
                return;
            }
            
            $http.post('api/?action=email_subscribe', { 'email': $scope.email }).success(function(response) {
            if (response.success) {
                $scope.email = "";
                $scope.error = "";
                $scope.success = "You have successfully subscribed to daily emails.";
            } else {
                if (response.email_exist) {
                    $scope.success = "";
                    $scope.error = "Sorry, this email has already subscribed to daily emails.";
                } else {
                    $scope.success = "";
                    $scope.error = "Sorry, an unexpected error occured. Please try again at a later time. If this problem continues, please <a href='https://zacharyseguin.ca/'>contact me</a>.";
                }
            }
            })
            .error(function() {
                $scope.success = '';
                $scope.error = "Sorry, an unexpected error occured. Please try again at a later time. If this problem continues, please <a href='https://zacharyseguin.ca/'>contact me</a>.";
            });

        };
    })
    // EmailUnsubscribe Controller
    // =================
    // Handles unsubscribing from daily emails.
    .controller('EmailUnsubscribe', function($scope, $http) {
       
        $scope.email = "";
        $scope.error = "";
        $scope.success = "";

        $scope.unsubscribe = function() {
            if ($scope.email == "" || $scope.email == undefined) {
                $scope.error = "Please enter your email address.";
                $scope.success = "";
                return;
            }
            
            $http.post('api/?action=email_unsubscribe', { 'email': $scope.email }).success(function(response) {
            if (response.success) {
                $scope.email = "";
                $scope.error = "";
                $scope.success = "You have successfully unsubscribed from daily emails.";
            } else {
                if (response.email_does_not_exist) {
                    $scope.success = "";
                    $scope.error = "Sorry, this email has not subscribed to daily emails.";
                } else {
                    $scope.success = "";
                    $scope.error = "Sorry, an unexpected error occured. Please try again at a later time. If this problem continues, please <a href='https://zacharyseguin.ca/'>contact me</a>.";
                }
            }
            })
            .error(function() {
                $scope.success = '';
                $scope.error = "Sorry, an unexpected error occured. Please try again at a later time. If this problem continues, please <a href='https://zacharyseguin.ca/'>contact me</a>.";
            });
        };
    });
