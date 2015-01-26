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

// sendEmail($to, $contents) Sends an HTML email to $to with the contents $contents
function sendEmail($to, $contents)
{
    global $config;
    return mail($to, "Daily Menu: You Want Food", $contents, "From: " . $config['FROM_EMAIL'] . "\r\nContent-Type: text/html\r\n");
}// End of sendEmail function

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

    $email = "<div style='font-family: sans-serif;'>";
    $email .= "<div style='background: #333; color: white; padding: 3px 15px;'><h1>You Want Food &ndash; Daily Menu</h1></div>";

    /** OUTLETS AND MENU **/
    $email .= "<div><h2>Outlets</h2>";

    foreach($menu as $outlet)
    {
        $email .= "<div style='border-left: 1px solid #eee; padding-left: 10px; margin-left: 5px;'>";

            $email .= "<h3>" . $outlet['outlet'] . "</h3>";

            $outlet_info = getOutletById($outlet['outlet_id'], $outlets);

            if ($outlet_info != null)
            {
                $email .= "<div style='font-size: .8em; padding: 0px; color: #555;'>";
                    if (isset($outlet_info['notice']))
                    {
                        $email .= "<p style='font-weight: bold;'>" . $outlet_info['notice'] . "</p>";
                    }// End of if

                    $email .= "<p>";

                        $today = today_hours($outlet_info['opening_hours'], $outlet_info['special_hours'], $outlet_info['dates_closed']);
                        if ($today['is_closed'])
                        {
                            $email .= "<b>Closed Today</b>";
                        }// End of if
                        else
                        {
                            $email .= "Open today: " . date('g:ia', $today['opening_hour']) . " &ndash; " . date('g:ia', $today['closing_hour']);
                        }// End of else

                        $email .= " | ";
                        $email .= "<a href='https://zacharyseguin.ca/projects/you-want-food/outlet/" . $outlet_info['outlet_id'] . "' style='color: #aa0000;'>Outlet Details</a>";

                    $email .= "</p>";
                $email .= "</div>";
            }// End of if

            $email .= "<h4>Lunch</h4>";

            if (empty($outlet['lunch']))
            {
                $email .= "<p style='color: #555;'>No menu was provided for this meal.</p>";
            }// End of if
            else
            {
                $email .= "<ul style='list-style: square; color: #555;'>";

                    foreach($outlet['lunch'] as $meal)
                    {
                        $email .= "<li>" . $meal['product_name'];
                        if (!empty($meal['diet_type']))
                        {
                            $email .= " <small> &ndash; " . $meal['diet_type'] . "</small>";
                        }// End of if

                        $email .= "</li>";
                    }// End of foreach

                $email .= "</ul>";
            }// End of else

            $email .= "<h4>Dinner</h4>";

            if (empty($outlet['dinner']))
            {
                $email .= "<p style='color: #555;'>No menu was provided for this meal.</p>";
            }// End of if
            else
            {
                $email .= "<ul style='list-style: square; color: #555;'>";

                    foreach($outlet['dinner'] as $meal)
                    {
                        $email .= "<li>" . $meal['product_name'];
                        if (!empty($meal['diet_type']))
                        {
                            $email .= " <small> &ndash; " . $meal['diet_type'] . "</small>";
                        }// End of if

                        $email .= "</li>";
                    }// End of foreach

                $email .= "</ul>";
            }// End of else

        $email .= "</div>";
    }// End of foreach

    $email .= "</div>";

    /** FOOTER **/
    $email .= "<p style='border-top: 1px solid #eee; padding-top: 10px; margin-bottom: 0px; color: #aaa; font-size: .8em;'>You are receiving this email because this email address was subscribed at <a href='https://zacharyseguin.ca/projects/you-want-food/' style='color: #888;'>https://zacharyseguin.ca/projects/you-want-food/</a>.</p>";
    $email .= "<p style='margin-top: 3px; color: #aaa; font-size: .8em;'>To stop receiving these emails, please visit <a href='https://zacharyseguin.ca/projects/you-want-food/email/unsubscribe' style='color: #888;'>https://zacharyseguin.ca/projects/you-want-food/email/unsubscribe</a>.</p>";

    $email .= "</div>";
    return $email;
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
    }// End of foreach

    $today_menu[] = $outlet_menu;
}// End of foreach

// do not continue if there is no menu information for today
if (!$info_found)
    die('No menu information for today.');

$menu_html = generateMenuHTML($outlets, $today_menu);

foreach ($subscribers as $s)
{
   if (!$email->sendEmail($s['email'], "You Want Food - Today's Menu", $menu_html))
      echo "Failed to send email to: " . $s['email'] . "\n";
}// End of foreach
