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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class InspectionsController
{
   private $db;

   public function __construct(DatabaseController $db)
   {
      $this->db = $db;
   }// End of constructor method

   public function uwaterlooAction($uwaterloo_id)
   {
      $facility_id = $this->db->getFacilityIdFromUWaterlooId($uwaterloo_id);
      if ($facility_id === FALSE) return new Response("", 500);

      return $this->facilityAction($facility_id);
   }// End of uwaterlooAction method

   public function facilitiesAction()
   {
      $facilities = $this->db->getInspectionsFacilities();

      if ($facilities === FALSE) return new Response("", 500);
      return new JsonResponse($facilities);
   }// End of facilitiesAction method

   public function facilityAction($facility_id)
   {
      $facility = $this->db->getInspectionsFacility($facility_id);
      if ($facility === FALSE) return new Response("", 500);
      $facility = $facility[0];

      $inspections = $this->db->getInspectionsInspectionsForFacility($facility_id);
      $facility['inspections'] = ($inspections !== FALSE) ? $inspections : array();

      foreach ($facility['inspections'] as &$i)
      {
         $infractions = $this->db->getInspectionsInfractionsForInspection($i['id']);
         $i['infractions'] = ($infractions !== FALSE) ? $infractions : array();
      }// End of for

      return new JsonResponse($facility);
   }// End of facilityAction method
}// End of InspectionsController class

?>
