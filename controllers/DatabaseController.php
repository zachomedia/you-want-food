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
      $this->conn = new \PDO("mysql:host=$hostname;port=$port;dbname=$db;charset=utf8", $user, $password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
      $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
   }// End of constructor method

   private function formatDate($strDate)
   {
      if ($strDate === null) return null;

      $date = new \DateTime($strDate);
      return $date->format(\DateTime::W3C);
   }// End of formatDate method

   public function getActiveEmailSubscriptions()
   {
      $stmt = $this->conn->prepare("SELECT email FROM email_subscriptions WHERE status=2");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute();
      return ($res) ? $stmt->fetchAll() : FALSE;
   }// End of getActiveEmailSubscriptions method

   public function subscribe($email, $ipaddress)
   {
      $stmt = $this->conn->prepare("SELECT email, status, token FROM email_subscriptions WHERE email = :email");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array("email" => $email));
      if ($res === FALSE) return FALSE;

      $results = $stmt->fetchAll();
      if (count($results) !== 0 && (int)$results[0]['status'] === 1) return $results[0]['token'];
      if (count($results) !== 0 && (int)$results[0]['status'] === 2) return 2;

      $token = sha1($email . time());
      if (count($results) !== 0 && (int)$results[0]['status'] === 0)
      {
         $stmt = $this->conn->prepare("UPDATE email_subscriptions SET status=1, token=:token WHERE email = :email");
         return $stmt->execute(array("email" => $email, "token" => $token)) ? $token : FALSE;
      }// End of if
      else
      {
         $stmt = $this->conn->prepare('INSERT INTO email_subscriptions (email, status, ipaddress, token) VALUES (:email, 1, :ipaddress, :token)');
         return $stmt->execute(array(
            'email' => $email,
            'ipaddress' => $ipaddress,
            'token' => $token
         )) ? $token : FALSE;
      }
   }// End of subscribe method

   public function confirmEmail($token)
   {
      if (strlen($token) === 0) return FALSE;

      $stmt = $this->conn->prepare("SELECT email, status FROM email_subscriptions WHERE token = :token");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array("token" => $token));
      if ($res === FALSE) return FALSE;
      $results = $stmt->fetchAll();

      if (count($results) === 0) return FALSE;

      $stmt = $this->conn->prepare("UPDATE email_subscriptions SET status=2, token='' WHERE token = :token");
      return $stmt->execute(array("token" => $token));
   }// End of confirmEmail method

   public function unsubscribe($email)
   {
      $stmt = $this->conn->prepare("SELECT email, status FROM email_subscriptions WHERE email = :email");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array("email" => $email));
      if ($res === FALSE) return FALSE;
      $results = $stmt->fetchAll();

      if (count($results) === 0 || (int)$results[0]['status'] !== 2) return 1;

      $stmt = $this->conn->prepare("UPDATE email_subscriptions SET status=0, token='' WHERE email = :email");
      return $stmt->execute(array("email" => $email));
   }// End of unsubscribe method

   public function getReviewsForOutlet($outlet_id, $moderation_status = 1)
   {
      $stmt = $this->conn->prepare("SELECT id, reviewer_name, review, date FROM outlet_reviews WHERE outlet_id = :outlet_id AND moderation_status = :moderation_status");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array("outlet_id" => $outlet_id, "moderation_status" => $moderation_status));
      if ($res === FALSE) return FALSE;

      $results = $stmt->fetchAll();
      if ($results === FALSE) return FALSE;

      foreach ($results as &$result)
      {
         $result['id'] = (int)$result['id'];
         $result['date'] = $this->formatDate($result['date']);
      }// End of foreach

      return $results;
   }// End of getReviewForOutlet method

   public function addOutletReview($outlet_id, $name, $email, $review, $ipaddress)
   {
      $moderation_token = sha1($outlet_id . $email . $ipaddress . time());

      $stmt = $this->conn->prepare('INSERT INTO outlet_reviews (outlet_id, reviewer_name, reviewer_email, review, ipaddress, moderation_token) VALUES (:outlet_id, :reviewer_name, :reviewer_email, :review, :ipaddress, :moderation_token)');

      return ($stmt->execute(array(
         'outlet_id' => $outlet_id,
         'reviewer_name' => utf8_encode($name),
         'reviewer_email' => utf8_encode($email),
         'review' => utf8_encode($review),
         'ipaddress' => $ipaddress,
         'moderation_token' => $moderation_token
      ))) ? $moderation_token : FALSE;
   }// End of addOutletReview method

   public function rejectOutletReview($moderation_token)
   {
      if (strlen($moderation_token) === 0) return FALSE;

      $stmt = $this->conn->prepare("SELECT moderation_status FROM outlet_reviews WHERE moderation_token = :moderation_token");
      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
      $res = $stmt->execute(array("moderation_token" => $moderation_token));
      if ($res === FALSE) return FALSE;
      $results = $stmt->fetchAll();

      if (count($results) === 0) return FALSE;

      $stmt = $this->conn->prepare("UPDATE outlet_reviews SET moderation_status=-1 WHERE moderation_token = :moderation_token");
      return $stmt->execute(array("moderation_token" => $moderation_token));
   }// End of confirmEmail method

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
      $results = $stmt->fetchAll();

      if ($results === FALSE) return FALSE;
      return $results;
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
         'name' => utf8_encode($name),
         'telephone' => utf8_encode($telephone),
         'street' => utf8_encode($street),
         'city' => utf8_encode($city),
         'eatsmart' => utf8_encode($eatsmart),
         'open_date' => utf8_encode($open_date),
         'description' => utf8_encode($description)
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
         'actions' => utf8_encode($actions),
         'charge_date' => $charge_date
      ));
   }// End of addInspectionsInspections method

   public function addInspectionsInfraction($id, $inspection_id, $type, $category_code, $letter_code, $description, $inspection_date, $charge_details)
   {
      $stmt = $this->conn->prepare('SELECT id FROM inspections_infractions WHERE id=:id');
      $stmt->execute(array('id' => $id));
      $results = $stmt->fetchAll();

      if (count($results) == 0)
         $stmt = $this->conn->prepare('INSERT INTO inspections_infractions (id, inspection_id, type, category_code, letter_code, description, inspection_date, charge_details) VALUES (:id, :inspection_id, :type, :category_code, :letter_code, :description, :inspection_date, :charge_details)');
      else
         $stmt = $this->conn->prepare('UPDATE inspections_infractions SET inspection_id = :inspection_id, type = :type, category_code = :category_code, letter_code = :letter_code, description = :description, inspection_date = :inspection_date, charge_details = :charge_details WHERE id = :id');

      return $stmt->execute(array(
         'id' => $id,
         'inspection_id' => $inspection_id,
         'type' => $type,
         'category_code' => utf8_encode($category_code),
         'letter_code' => utf8_encode($letter_code),
         'description' => utf8_encode($description),
         'inspection_date' => $inspection_date,
         'charge_details' => utf8_encode($charge_details)
      ));
   }// End of addInspectionInfraction method
}// End of DatabaseController method

?>
