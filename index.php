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
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

$app['database.controller'] = $app->share(function() {
   return new Controller\DatabaseController(DATABASE_HOSTNAME, DATABASE_PORT, DATABASE_DB, DATABASE_USER, DATABASE_PASSWORD);
});

$app['email.controller'] = $app->share(function() {
   return new Controller\EmailController(EMAIL_HOSTNAME, EMAIL_PORT, EMAIL_SSL, EMAIL_USER, EMAIL_PASSWORD, json_decode(EMAIL_FROM, true));
});

$app['frontend.controller'] = $app->share(function() {
   return new Controller\FrontendController();
});

$app['uwaterloo-api.controller'] = $app->share(function() {
   return new Controller\UWaterlooAPIController(UWATERLOO_API_KEY, UWATERLOO_API_BASE);
});

$app['email-subscription.controller'] = $app->share(function($app) {
   return new Controller\EmailSubscriptionController($app['database.controller'], $app['email.controller']);
});

$app['reviews.controller'] = $app->share(function($app) {
   return new Controller\ReviewsController($app['database.controller'], $app['email.controller']);
});

$app['inspections.controller'] = $app->share(function($app) {
   return new Controller\InspectionsController($app['database.controller']);
});

$app->get('/', "frontend.controller:frontendAction");
$app->get('/about', "frontend.controller:frontendAction");
$app->get('/outlet/{outlet_id}', "frontend.controller:frontendAction")
   ->assert('outlet_id', '\d+');
$app->get('/email/subscribe', "frontend.controller:frontendAction")
   ->bind('/email/subscribe');
$app->get('/email-subscribe', function() use($app) {
   return $app->redirect($app['url_generator']->generate('/email/subscribe'), 301);
});
$app->get('/email/unsubscribe', "frontend.controller:frontendAction")
   ->bind('/email/unsubscribe');
$app->get('/email-unsubscribe', function() use($app) {
   return $app->redirect($app['url_generator']->generate('/email/unsubscribe'), 301);
});
$app->get('/email/confirmed', "frontend.controller:frontendAction")
   ->bind('/email/confirmed');
$app->get('/email/confirmation-error', "frontend.controller:frontendAction")
   ->bind('/email/confirmation-error');

$app->get('/email/confirm/{token}', "email-subscription.controller:confirmAction");

$app->get('/api/outlets.json', "uwaterloo-api.controller:outletsAction");
$app->get('/api/menu.json', "uwaterloo-api.controller:menuAction");
$app->get('/api/menu/{outlet_id}.json', "uwaterloo-api.controller:menuForOutletAction")
   ->assert('outlet_id', '\d+');

$app->post('/api/email/subscribe.json', "email-subscription.controller:subscribeAction");
$app->post('/api/email/unsubscribe.json', "email-subscription.controller:unsubscribeAction");

$app->get('/api/reviews/outlet/{outlet_id}.json', 'reviews.controller:outletAction')
   ->assert('outlet_id', '\d+');
$app->post('/api/reviews/outlet/{outlet_id}/add.json', 'reviews.controller:addOutletReviewAction')
   ->assert('outlet_id', '\d+');
$app->get('/reviews/outlets/reject/{moderation_token}', 'reviews.controller:rejectOutletReviewAction');

$app->get('/api/inspections/facilities.json', 'inspections.controller:facilitiesAction');
$app->get('/api/inspections/facility/{facility_id}.json', 'inspections.controller:facilityAction');
$app->get('/api/inspections/uwaterloo/{uwaterloo_id}.json', 'inspections.controller:uwaterlooAction')
   ->assert('uwaterloo_id', '\d+');

$app->run();

?>
