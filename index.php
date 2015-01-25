<?php

/*
    Copyright (c) 2015 Zachary Seguin

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

namespace ZacharySeguin\YouWantFood;

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config.php');

$app = new \Silex\Application();
$app['debug'] = true;
$app->register(new \Silex\Provider\ServiceControllerServiceProvider());

$app['database.controller'] = $app->share(function() {
   return new Controller\DatabaseController(DATABASE_HOSTNAME, DATABASE_PORT, DATABASE_DB, DATABASE_USER, DATABASE_PASSWORD);
});

$app['frontend.controller'] = $app->share(function() {
   return new Controller\FrontendController();
});

$app['uwaterloo-api.controller'] = $app->share(function() {
   return new Controller\UWaterlooAPIController(UWATERLOO_API_KEY, UWATERLOO_API_BASE);
});

$app['email-subscription.controller'] = $app->share(function($app) {
   return new Controller\EmailSubscriptionController($app['database.controller']);
});

$app['inspections.controller'] = $app->share(function($app) {
   return new Controller\InspectionsController($app['database.controller']);
});

$app->get('/', "frontend.controller:frontendAction");
$app->get('/about', "frontend.controller:frontendAction");
$app->get('/outlet/{outlet_id}', "frontend.controller:frontendAction")
   ->assert('outlet_id', '\d+');
$app->get('/email-subscribe', "frontend.controller:frontendAction");
$app->get('/email-unsubscribe', "frontend.controller:frontendAction");

$app->get('/api/outlets.json', "uwaterloo-api.controller:outletsAction");
$app->get('/api/menu.json', "uwaterloo-api.controller:menuAction");
$app->get('/api/menu/{outlet_id}.json', "uwaterloo-api.controller:menuForOutletAction")
   ->assert('outlet_id', '\d+');

$app->post('/api/email/subscribe.json', "email-subscription.controller:subscribeAction");
$app->post('/api/email/unsubscribe.json', "email-subscription.controller:unsubscribeAction");

$app->get('/api/inspections/facilities.json', 'inspections.controller:facilitiesAction');
$app->get('/api/inspections/facility/{facility_id}.json', 'inspections.controller:facilityAction');
$app->get('/api/inspections/uwaterloo/{uwaterloo_id}.json', 'inspections.controller:uwaterlooAction')
   ->assert('uwaterloo_id', '\d+');

$app->run();

?>
