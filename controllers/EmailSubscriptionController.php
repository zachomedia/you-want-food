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

namespace ZacharySeguin\YouWantFood\Controller;

require_once(__DIR__ . '/../vendor/autoload.php');

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailSubscriptionController
{
   private $db;
   private $email;

   public function __construct(DatabaseController $db, EmailController $email)
   {
      $this->db = $db;
      $this->email = $email;
   }// End of constructor method

   private function getEmail(Request $request)
   {
      if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) return FALSE;

      $data = json_decode($request->getContent(), true);
      if (!is_array($data)) return FALSE;
      if (!isset($data['email'])) return FALSE;
      $data['email'] = trim($data['email']);
      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return FALSE;
      return $data['email'];
   }// End of getEmail method

   public function subscribeAction(Request $request)
   {
      $email = $this->getEmail($request);
      if ($email === FALSE) return new JsonResponse(array('error' => 'Please enter a valid email address.'), 400);

      $response = $this->db->subscribe($email, $request->getClientIp());
      if ($response === FALSE) return new JsonResponse(array('error' => 'Sorry, an unexpected error occured while subscribing. Please try again at a later time.'), 500);
      if ($response === 2) return new JsonResponse(array('error' => 'This email address is already subscribed to emails.'), 400);

      $this->email->sendConfirmationEmail($email, $response);
      return new Response("");
   }// End of subscribeAction method

   public function confirmAction($token, Application $app)
   {
      if (strlen($token) === 0) return $app->redirect($app['url_generator']->generate('/email/confirmation-error'));

      if ($this->db->confirmEmail($token))
         return $app->redirect($app['url_generator']->generate('/email/confirmed'));
      else
         return $app->redirect($app['url_generator']->generate('/email/confirmation-error'));
   }// End of confirmAction method

   public function unsubscribeAction(Request $request)
   {
      $email = $this->getEmail($request);
      if ($email === FALSE) return new JsonResponse(array('error' => 'Please enter a valid email address.'), 400);

      $response = $this->db->unsubscribe($email);
      if ($response === FALSE) return new JsonResponse(array('error' => 'Sorry, an unexpected error occured while subscribing. Please try again at a later time.'), 500);
      if ($response === 1) return new JsonResponse(array('error' => 'This email address is not subscribed to emails.'), 400);

      return new Response("");
   }// End of unsubscribeAction method
}// End of EmailSubscriptionController class

?>
