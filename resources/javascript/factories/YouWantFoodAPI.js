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
                 cache.outlets = $resource('./api/outlets.json', {}, { get: { method: 'GET', cache: true }});
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
           },

           inspections: function(id) {
              if (!cache.inspections) {
                 cache.inspections = [];
              }

              if (!cache.inspections[id]) {
                 cache.inspections[id] = $resource('./api/inspections/uwaterloo/:outlet_id.json', {}, { get: {method: 'GET', params: {outlet_id: id}, isArray: false, cache: true}});
              }

              return cache.inspections[id];
           },

           menu: function(id) {
              if (!cache.menu) {
                 cache.menu = [];
              }

              if (!cache.menu[id]) {
                 cache.menu[id] = $resource('./api/menu/:outlet_id.json', {outlet_id: '@outlet_id'}, { get: {method: 'GET', params: {outlet_id: id}, isArray: false, cache: true}});
              }

              return cache.menu[id];
           },

           subscribe: function() {
               if (!cache.subscribe) {
                   cache.subscribe = $resource('./api/email/subscribe.json', {}, { post: {method: 'POST'}});
               }

               return cache.subscribe;
           },

           unsubscribe: function() {
               if (!cache.unsubscribe) {
                   cache.unsubscribe = $resource('./api/email/unsubscribe.json', {}, { post: {method: 'POST'}});
               }

               return cache.unsubscribe;
           }

        };

        return YouWantFoodAPI;
    }]);
