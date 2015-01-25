<?php

namespace ZacharySeguin\YouWantFood;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../controllers/DatabaseController.php');
require_once(__DIR__ . '/../config.php');

ini_set('memory_limit', '2048M');

header('Content-Type: text/plain');

$db = new Controller\DatabaseController(DATABASE_HOSTNAME, DATABASE_PORT, DATABASE_DB, DATABASE_USER, DATABASE_PASSWORD);

$csv = new \parseCSV(__DIR__ . '/../data/Inspections/Facilities_OpenData.csv');
$csv->encoding('UTF-16', 'UTF-8');
$facilities = $csv->data;

foreach ($facilities as $f)
{
   $db->addInspectionsFacility($f['FACILITYID'], $f['BUSINESS_NAME'], $f['TELEPHONE'], $f['ADDR'], $f['CITY'], $f['EATSMART'], $f['OPEN_DATE'], $f['DESCRIPTION']);
}// End of for

$csv = new \parseCSV(__DIR__ . '/../data/Inspections/Inspections_OpenData.csv');
$csv->encoding('UTF-16', 'UTF-8');
$inspections = $csv->data;

foreach ($inspections as $i)
{
   $db->addInspectionsInspections($i['INSPECTION_ID'], $i['FACILITYID'], $i['INSPECTION_DATE'], $i['REQUIRE_REINSPECTION'] === "Y", $i['CERTIFIED_FOOD_HANDLER'] === "Yes", $i['INSPECTION_TYPE'], $i['CHARGE_REVOCKED'], $i['Actions'], $i['CHARGE_DATE']);
}// End of for

$csv = new \parseCSV(__DIR__ . '/../data/Inspections/Infractions_OpenData.csv');
$csv->encoding('UTF-16', 'UTF-8');
$infractions = $csv->data;

foreach ($infractions as $i)
{
   $db->addInspectionsInfraction($i['INFRACTION_ID'], $i['INSPECTION_ID'], $i['INFRACTION_TYPE'], $i['category_code'], $i['letter_code'], $i['Description1'], $i['InspectionDate'], $i['ChargeDetails']);
}// End of for
