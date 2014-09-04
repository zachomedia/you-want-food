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
    .factory('YouWantFoodAPI', ['$q', '$resource', function($q, $resource) {

        var cache = {};

        // API Functions
        var YouWantFoodAPI = {

            outlets: function() {
                if (!cache.outlets) {
                    cache.outlets = $resource('api/?data=outlets', {}, { get: { cache: true }} );
                }

                return cache.outlets;
            },

            outlet: function(id) {
                var deferred = $q.defer();

                this.outlets().query(function(outlets) {
                    for (var indx in outlets) {
                        if (outlets[indx].outlet_id == id) {
                            deferred.resolve(outlets[indx]);
                            break;
                        }
                    }

                    deferred.reject('outlet not found');
                });

                return deferred.promise;
            }

        };

        return YouWantFoodAPI;
    }]);
