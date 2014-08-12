<?php

/*

    Copyright (c) 2014 Zachary Seguin

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

// Requrie the API configuration (for database connection and email settings)
require 'config.php';

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
}

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
                        $email .= "<a href='https://zacharyseguin.ca/projects/you-want-food/#/outlet/" . $outlet_info['outlet_id'] . "' style='color: #aa0000;'>Outlet Details</a>";

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
    $email .= "<p style='margin-top: 3px; color: #aaa; font-size: .8em;'>To stop receiving these emails, please visit <a href='https://zacharyseguin.ca/projects/you-want-food/#/email-unsubscribe' style='color: #888;'>https://zacharyseguin.ca/projects/you-want-food/#/email-unsubscribe</a>.</p>";

    $email .= "</div>";
    return $email;
}// End of generateMenuHTML function

// Load the menu information from the API
$outlets = json_decode(file_get_contents('https://zacharyseguin.ca/projects/you-want-food/api?data=outlets'), true);
$menu = json_decode(file_get_contents('https://zacharyseguin.ca/projects/you-want-food/api?data=menu'), true);

// Get only menu information for today
$today = date('Y-m-d');
$today_menu = Array();

$info_found = false;

foreach ($menu as $outlet)
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

// Get email addresses to send emails to
try {
    $conn = new PDO('mysql:host=' . $config['DATABASE_HOSTNAME'] . ';dbname=' . $config['DATABASE_DATABASE'], $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare('SELECT email FROM email_subscriptions WHERE enabled = 1');
    $stmt->execute();

    $results = $stmt->fetchAll();

    foreach ($results as $result) {
        sendEmail($result['email'], $menu_html);
    }
} catch (PDOException $e) {
    die($e->getMessage());
}
