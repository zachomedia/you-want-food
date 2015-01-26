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

use Symfony\Component\HttpFoundation\JsonResponse;

class UWaterlooAPIController
{
   private $key;
   private $base;

   public function __construct($key, $base = "https://api.uwaterloo.ca/v2")
   {
      $this->key = $key;
      $this->base = $base;
   }// End of constructor method

   protected function executeRequest($method, $params = array())
   {
      $params['key'] = $this->key;
      $url = $this->base . $method . ".json?" . http_build_query($params);

      $response = json_decode(file_get_contents($url), true);

      if ($response === FALSE) return $response;
      if (isset($response['meta']))
      {
         if (isset($response['meta']['status']))
         {
            if ($response['meta']['status'] !== 200)
            {
               return FALSE;
            }// End of if
         }// End of if
      }// End of if

      if (isset($response['data'])) return $response['data'];
      return FALSE;
   }// End of executeRequest method

   public function getOutlets()
   {
      return $this->executeRequest("/foodservices/locations");
   }// End of getOutlets method

   public function getMenu()
   {
      return $this->executeRequest("/foodservices/menu");
   }// End of getOutlets method

   public function outletsAction()
   {
      $response = $this->getOutlets();
      return ($response !== FALSE) ? new JsonResponse($response) : new JsonResponse(array("error" => "An error occured reading the response from the University of Waterloo API."), 500);
   }// End of outletsAction method

   public function menuAction()
   {
      $response = $this->getMenu();
      return ($response !== FALSE) ? new JsonResponse($response) : new JsonResponse(array("error" => "An error occured reading the response from the University of Waterloo API."), 500);
   }// End of menuAction method

   public function menuForOutletAction($outlet_id)
   {
      $response = $this->getMenu();

      if ($response === FALSE) return JsonResponse(array("error" => "An error occured reading the response from the University of Waterloo API."), 500);

      if (!isset($response['outlets'])) return JsonResponse(array("error" => "An error occured reading the response from the University of Waterloo API."), 500);

      foreach ($response['outlets'] as $outlet)
      {
         if ($outlet['outlet_id'] === (int)$outlet_id)
         {
            return new JsonResponse($outlet);
         }// End of if
      }// End of foreach

      return new JsonResponse(array("error" => "No menu information available for this location."), 404);
   }// End of menuForOutletAction method
}// End of UWaterlooAPIController class

?>
