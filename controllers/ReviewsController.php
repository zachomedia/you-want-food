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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReviewsController
{
   private $db;

   public function __construct(DatabaseController $db)
   {
      $this->db = $db;
   }// End of constructor method

   private function getPostData(Request $request)
   {
      if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) return FALSE;

      $data = json_decode($request->getContent(), true);
      if (!is_array($data)) return FALSE;
      return $data;
   }// End of getPostData method

   public function outletAction($outlet_id)
   {
      $reviews = $this->db->getReviewsForOutlet($outlet_id);
      if ($reviews === FALSE) return new Response("", 500);

      return new JsonResponse($reviews);
   }// End of outletAction method

   public function addOutletReviewAction($outlet_id, Request $request)
   {
      $data = $this->getPostData($request);
      if ($data === FALSE) return new Response("", 400);

      // Validate required information
      $data['name'] = trim($data['name']);
      $data['email'] = trim($data['email']);
      $data['review'] = trim($data['review']);

      if (strlen($data['name']) === 0) return new JsonResponse(array("error" => "Please provide your name."), 400);
      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return new JsonResponse(array("error" => "Please provide a valid email address. Your email address will not be show publicly."), 400);
      if (strlen($data['review']) === 0) return new JsonResponse(array("error" => "Please provide a review."), 400);

      $res = $this->db->addOutletReview($outlet_id, $data['name'], $data['email'], $data['review'], $request->getClientIp());

      if ($res !== FALSE) return new Response("");
      return new Response(array("error" => "Sorry, an unexpected error ocurred."), 500);
   }// End of addOutletReviewAction method
}// End of ReviewsController class

?>
