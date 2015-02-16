<?php

namespace ZacharySeguin\YouWantFood;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../controllers/DatabaseController.php');
require_once(__DIR__ . '/../controllers/EmailController.php');
require_once(__DIR__ . '/../controllers/UWaterlooAPIController.php');
require_once(__DIR__ . '/../config.php');

// Modified from: https://github.com/uWaterloo/Parsers/blob/master/FoodServices/is_open_now.php
function today_hours($opening, $special, $closed)
{
    // Get hours for today
    $today             = time();
    $today_date        = date('Y-m-d', $today);
    $today_weekday     = strtolower(date('l', $today));

    $today_start     = (int) strtotime($today_date . ' ' . $opening[$today_weekday]['opening_hour']);
    $today_end       = (int) strtotime($today_date . ' ' . $opening[$today_weekday]['closing_hour']);
    $today_closed    = $opening[$today_weekday]['is_closed'];

    // Find out if yesterday and/or today are operating under special hours
    foreach ($special as $i)
    {
        if ($i['date'] == $today_date)
        {
            $today_start  = (int) strtotime($today_date . ' ' . $i['opening_hour']);
            $today_end    = (int) strtotime($today_date . ' ' . $i['closing_hour']);
            $today_closed = false;
        }
    }

    if (in_array($today_date, $closed))
    {
        $today_closed = true;
    }

    return Array('opening_hour' => $today_start, 'closing_hour' => $today_end, 'is_closed' => $today_closed);
}// End of today_hours function

function getOutletById($id, $outlets)
{
    foreach($outlets as $outlet)
    {
        if ($outlet['outlet_id'] == $id)
            return $outlet;
    }// End of foreach

    return null;
}// End of getOutletById

// generateMenuHTML($menu) Generates the HTML for the $menu.
function generateMenuHTML($outlets, $menu)
{
   $APP_BASE = APP_BASE;
   $today = date('l, F jS, Y');
   $html = <<<EOM

<div style="font-family: sans-serif">

   <div id="header" style="padding: 10px; text-align: center;">
      <h1>You Want Food &mdash; Today's Menu</h1>
      <p>$today</p>
      <p><a href="${APP_BASE}" style="text-decoration: none; color: #aa0000; padding: 10px;">Visit You Want Food</a></p>
   </div>

   <div id="new-features" style="font-size: 0.9em; margin: 10px; padding: 10px;">
      <h2>New Features</h2>
      <p>You Want Food development continues:</p>

      <div style="margin: 10px;">
         <h3>Region of Waterloo Public Health Inspection Results</h3>
         <p>The You Want Food web application now displays results from inspections by the Region of Waterloo Public Health.</p>
         <p>To view inspection results, view an outlet's details and select the "Public Health Inspections" tab.</p>
      </div>

      <div style="margin: 10px;">
         <h3>Outlet Reviews</h3>
         <p>Share your experience at the various food outlets on campus by writing a review.</p>
         <p>View an outlet's details and select the "Reviews" tab.</p>
      </div>

      <div style="margin: 10px;">
         <h3>Updated Menu Email</h3>
         <p>The daily menu email has been given a new look. Don't hesitate to pass any feedback to <a href="mailto:youwantfood@zacharyseguin.ca">youwantfood@zacharyseguin.ca</a>.</p>
      </div>
   </div>

   <hr>

EOM;

foreach ($menu as $outlet)
{
   $outlet_details = getOutletById($outlet['outlet_id'], $outlets);

   $hours = today_hours($outlet_details['opening_hours'], $outlet_details['special_hours'], $outlet_details['dates_closed']);
   $hours_string = ($hours['is_closed']) ? "<b>CLOSED TODAY</b>" : "<b>OPEN</b>: " . date('g:ia', $hours['opening_hour']) . " &ndash; " . date('g:ia', $hours['closing_hour']);

   $html .= <<<EOM

      <div style=" background: #f4f4f4; padding: 10px; margin: 10px; border: 1px solid #eee; border-left: 6px solid #eee;">

         <h2>{$outlet['outlet']}</h2>
         <p style="font-size: 0.9em">$hours_string | <a href="{$APP_BASE}outlet/{$outlet['outlet_id']}" style="color: #aa0000;">Outlet Details</a></p>
         <p style="font-weight: bold">{$outlet_details['notice']}</p>

EOM;

   $lunch = $outlet['lunch'];
   $html .= "<div><h3>Lunch</h3><ul style='list-style: square;'>";

   foreach ($lunch as $meal)
   {
      $diet_type_string = (!empty($meal['diet_type'])) ? "[{$meal['diet_type']}]" : "";
      $html .= <<<EOM

         <li style="margin: 5px 0;">{$meal['product_name']} $diet_type_string</li>

EOM;
   }// End of foreach
   if (count($lunch) === 0) $html .= "Sorry, no menu was provided for this meal.";
   $html .= "</ul></div>";

   $dinner = $outlet['dinner'];
   $html .= "<div><h3>Dinner</h3><ul style='list-style: square;'>";

   foreach ($dinner as $meal)
   {
      $diet_type_string = (!empty($meal['diet_type'])) ? "[{$meal['diet_type']}]" : "";
      $html .= <<<EOM

         <li style="margin: 5px 0;">{$meal['product_name']} $diet_type_string</li>

EOM;
   }// End of foreach
   if (count($dinner) === 0) $html .= "Sorry, no menu was provided for this meal.";
   $html .= "</ul></div>";

   $html .= "<p style='font-weight: bold'>" . $outlet['notes'] . "</p>";

   $html .= "</div>";
}// End of foreach

   $html .= <<<EOM
      <p style='padding-top: 10px; margin-bottom: 0px; color: #888; font-size: .8em; text-align: center;'>You are receiving this email because this email address was subscribed at <a href='$APP_BASE' style='color: #888;'>$APP_BASE</a>.</p>
      <p style='margin-top: 3px; color: #888; font-size: .8em; text-align: center;'>To stop receiving these emails, please visit <a href='${APP_BASE}email/unsubscribe' style='color: #888;'>${APP_BASE}email/unsubscribe</a>.</p>
</div>
EOM;

   return $html;
}// End of generateMenuHTML function

// Initialize
$db = new Controller\DatabaseController(DATABASE_HOSTNAME, DATABASE_PORT, DATABASE_DB, DATABASE_USER, DATABASE_PASSWORD);
$email = new Controller\EmailController(EMAIL_HOSTNAME, EMAIL_PORT, EMAIL_SSL, EMAIL_USER, EMAIL_PASSWORD, json_decode(EMAIL_FROM, true));
$uwapi = new Controller\UWaterlooAPIController(UWATERLOO_API_KEY, UWATERLOO_API_BASE);

// Load data
$outlets = $uwapi->getOutlets();
$menu = $uwapi->getMenu();
$subscribers = $db->getActiveEmailSubscriptions();

if ($outlets === FALSE || $menu === FALSE || $subscribers === FALSE)
   die('Failed to load data');

// Get only menu information for today
$today = date('Y-m-d');
$today_menu = Array();

$info_found = false;

foreach ($menu['outlets'] as $outlet)
{
    $outlet_menu = Array(
        'outlet' => $outlet['outlet_name'],
        'outlet_id' => $outlet['outlet_id']
    );

    foreach ($outlet['menu'] as $day)
    {
        if ($day['date'] != $today)
            continue;

        $info_found = true;

        $outlet_menu['lunch'] = $day['meals']['lunch'];
        $outlet_menu['dinner'] = $day['meals']['dinner'];

        if (!empty($day['notes']))
            $outlet_menu['notes'] = $day['notes'];
        else
           $outlet_menu['notes'] = '';
    }// End of foreach

    $today_menu[] = $outlet_menu;
}// End of foreach

// do not continue if there is no menu information for today
if (!$info_found)
    die('No menu information for today.');

$menu_html = generateMenuHTML($outlets, $today_menu);
$APP_BASE = APP_BASE;

foreach ($subscribers as $s)
{
   if (!$email->sendEmail($s['email'], "You Want Food - Today's Menu", $menu_html, "Sorry, menu is only available in HTML format. Visit {$APP_BASE} for menu information."))
      echo "Failed to send email to: " . $s['email'] . "\n";
}// End of foreach
