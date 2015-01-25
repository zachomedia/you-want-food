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

class DatabaseController
{
   private $conn;

   public function __construct($hostname, $port, $db, $user, $password)
   {
      $this->conn = new \PDO("mysql:host=$hostname;port=$port;dbname=$db", $user, $password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
      $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
   }// End of constructor method

   public function getInspectionsFacilities()
   {
      $stmt = $this->conn->prepare("SELECT * FROM inspections_facilities");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute();

      if ($res === FALSE) return FALSE;
      return $stmt->fetchAll();
   }// End of getInspectionsFacilities method

   public function getInspectionsFacility($id)
   {
      $stmt = $this->conn->prepare("SELECT * FROM inspections_facilities WHERE id = :id");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array('id' => $id));

      if ($res === FALSE) return FALSE;
      return $stmt->fetchAll();
   }// End of getInspectionsFacility method

   public function getFacilityIdFromUWaterlooId($uwaterloo_id)
   {
      $stmt = $this->conn->prepare("SELECT facility_id FROM facility_mappings WHERE id = :id");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array('id' => $uwaterloo_id));
      if ($res === FALSE) return FALSE;

      $rows = $stmt->fetchAll();
      if (count($rows) == 0) return FALSE;

      return $rows[0]['facility_id'];
   }// End of getFacilityIdFromUWaterlooId method

   public function getInspectionsInspectionsForFacility($facility_id)
   {
      $stmt = $this->conn->prepare("SELECT * FROM inspections_inspections WHERE facility_id = :facility_id ORDER BY inspection_date");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array('facility_id' => $facility_id));

      if ($res === FALSE) return FALSE;
      return $stmt->fetchAll();
   }// End of getInspectionsInspectionsForFacility method

   public function getInspectionsInfractionsForInspection($inspection_id)
   {
      $stmt = $this->conn->prepare("SELECT * FROM inspections_infractions WHERE inspection_id = :inspection_id");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array('inspection_id' => $inspection_id));

      if ($res === FALSE) return FALSE;
      return $stmt->fetchAll();
   }// End of getInspectionsInfractionsForInspection method

   public function addInspectionsFacility($id, $name, $telephone, $street, $city, $eatsmart, $open_date, $description)
   {
      $stmt = $this->conn->prepare('SELECT id FROM inspections_facilities WHERE id=:id');
      $stmt->execute(array('id' => $id));
      $results = $stmt->fetchAll();

      if (count($results) == 0)
         $stmt = $this->conn->prepare('INSERT INTO inspections_facilities (id, name, telephone, street, city, eatsmart, open_date, description) VALUES (:id, :name, :telephone, :street, :city, :eatsmart, :open_date, :description)');
      else
         $stmt = $this->conn->prepare('UPDATE inspections_facilities SET name = :name, telephone = :telephone, street = :street, city = :city, eatsmart = :eatsmart, open_date = :open_date, description = :description WHERE id = :id');

      return $stmt->execute(array(
         'id' => $id,
         'name' => $name,
         'telephone' => $telephone,
         'street' => $street,
         'city' => $city,
         'eatsmart' => $eatsmart,
         'open_date' => $open_date,
         'description' => $description
      ));
   }// End of addInspectionsFacility method

   public function addInspectionsInspections($id, $facility_id, $inspection_date, $require_reinspection, $certified_food_handler, $inspection_type, $charge_revoked, $actions, $charge_date)
   {
      $stmt = $this->conn->prepare('SELECT id FROM inspections_inspections WHERE id=:id');
      $stmt->execute(array('id' => $id));
      $results = $stmt->fetchAll();

      if (count($results) == 0)
         $stmt = $this->conn->prepare('INSERT INTO inspections_inspections (id, facility_id, inspection_date, require_reinspection, certified_food_handler, inspection_type, actions, charge_revoked, charge_date) VALUES (:id, :facility_id, :inspection_date, :require_reinspection, :certified_food_handler, :inspection_type, :actions, :charge_revoked, :charge_date)');
      else
         $stmt = $this->conn->prepare('UPDATE inspections_inspections SET facility_id = :facility_id, inspection_date = :inspection_date, require_reinspection = :require_reinspection, certified_food_handler = :certified_food_handler, inspection_type = :inspection_type, actions = :actions, charge_revoked = :charge_revoked, charge_date = :charge_date WHERE id = :id');

      return $stmt->execute(array(
         'id' => $id,
         'facility_id' => $facility_id,
         'inspection_date' => $inspection_date,
         'require_reinspection' => $require_reinspection,
         'certified_food_handler' => $certified_food_handler,
         'inspection_type' => $inspection_type,
         'charge_revoked' => $charge_revoked,
         'actions' => $actions,
         'charge_date' => $charge_date
      ));
   }// End of addInspectionsInspections method

   public function addInspectionsInfraction($id, $inspection_id, $type, $category_code, $letter_code, $description, $inspection_date, $charge_details)
   {
      $stmt = $this->conn->prepare('SELECT id FROM inspections_infractions WHERE id=:id');
      $stmt->execute(array('id' => $id));
      $results = $stmt->fetchAll();

      if (count($results) == 0)
         $stmt = $this->conn->prepare('INSERT INTO inspections_infractions(id, inspection_id, type, category_code, letter_code, description, inspection_date, charge_details) VALUES (:id, :inspection_id, :type, :category_code, :letter_code, :description, :inspection_date, :charge_details)');
      else
         $stmt = $this->conn->prepare('UPDATE inspections_infractions SET inspection_id = :inspection_id, type = :type, category_code = :category_code, letter_code = :letter_code, description = :description, inspection_date = :inspection_date, charge_details = :charge_details WHERE id = :id');

      return $stmt->execute(array(
         'id' => $id,
         'inspection_id' => $inspection_id,
         'type' => $type,
         'category_code' => $category_code,
         'letter_code' => $letter_code,
         'description' => $description,
         'inspection_date' => $inspection_date,
         'charge_details' => $charge_details
      ));
   }// End of addInspectionInfraction method
}// End of DatabaseController method

?>
