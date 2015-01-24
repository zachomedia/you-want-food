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

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailSubscriptionController
{
   private function getEmail(Request $request)
   {
      if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) return FALSE;

      $data = json_decode($request->getContent(), true);
      if (!is_array($data)) return FALSE;
      if (!isset($data['email'])) return FALSE;
      return $data['email'];
   }// End of getEmail method

   public function subscribeAction(Request $request)
   {
      $email = $this->getEmail($request);

      return new JsonResponse(array('success' => false, 'error' => 'Sorry, subscriptions to You Want Food are currently unavailable. Please check back at a later time.'));
   }// End of subscribeAction method

   public function unsubscribeAction(Request $request)
   {
      $email = $this->getEmail($request);
      if ($email === FALSE) return Response("", 400);

      return new JsonResponse(array('success' => false, 'error' => 'Sorry, subscriptions (including unsubscription) to You Want Food are currently unavailable. Please check back at a later time.'));
   }// End of unsubscribeAction method
}// End of EmailSubscriptionController class

?>
