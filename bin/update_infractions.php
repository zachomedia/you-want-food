<?php

namespace ZacharySeguin\YouWantFood;

define('DATA_URL', 'http://www.regionofwaterloo.ca/opendatadownloads/Inspections.zip');
define('ZIP_PATH', '/tmp/inspections.zip');
define('DATA_PATH', '/tmp/inspections');

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../controllers/DatabaseController.php');
require_once(__DIR__ . '/../config.php');

ini_set('memory_limit', '2048M');

// Download the zip file
$handle = fopen(ZIP_PATH, "w");
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, DATA_URL);
curl_setopt($curl, CURLOPT_FAILONERROR, true);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_AUTOREFERER, true);
curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
curl_setopt($curl, CURLOPT_TIMEOUT, 100);
curl_setopt($curl, CURLOPT_FILE, $handle);
$page = curl_exec($curl);

if (!$page)
{
   die('Download Failed: ' . curl_error($curl));
}// End of if

curl_close($curl);

$zip = new \ZipArchive();
if ($zip->open(ZIP_PATH) != "true")
{
   die('Unzip Failed: Unable to open Zip file');
}// End of if

$zip->extractTo(DATA_PATH);
$zip->close();

$db = new Controller\DatabaseController(DATABASE_HOSTNAME, DATABASE_PORT, DATABASE_DB, DATABASE_USER, DATABASE_PASSWORD);

$csv = new \parseCSV(DATA_PATH . '/Facilities_OpenData.csv');
$csv->encoding('UTF-16', 'UTF-8');
$facilities = $csv->data;

foreach ($facilities as $f)
{
   $db->addInspectionsFacility($f['FACILITYID'], $f['BUSINESS_NAME'], $f['TELEPHONE'], $f['ADDR'], $f['CITY'], $f['EATSMART'], $f['OPEN_DATE'], $f['DESCRIPTION']);
}// End of for

$csv = new \parseCSV(DATA_PATH . '//Inspections_OpenData.csv');
$csv->encoding('UTF-16', 'UTF-8');
$inspections = $csv->data;

foreach ($inspections as $i)
{
   $db->addInspectionsInspections($i['INSPECTION_ID'], $i['FACILITYID'], $i['INSPECTION_DATE'], $i['REQUIRE_REINSPECTION'] === "Y", $i['CERTIFIED_FOOD_HANDLER'] === "Yes", $i['INSPECTION_TYPE']);
}// End of for

$csv = new \parseCSV(DATA_PATH . '/Infractions_OpenData.csv');
$csv->encoding('UTF-16', 'UTF-8');
$infractions = $csv->data;

foreach ($infractions as $i)
{
   $db->addInspectionsInfraction($i['INFRACTION_ID'], $i['INSPECTION_ID'], $i['INFRACTION_TYPE'], $i['Infraction'], $i['Result'], $i['Comment'], $i['InspectionDate']);
}// End of for
