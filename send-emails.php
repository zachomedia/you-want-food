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

// generateMenuHTML($menu) Generates the HTML for the $menu.
function generateMenuHTML($menu)
{
    $email = '';

    $email .= "<h1>You Want Food - Daily Menu</h1>";

    foreach ($menu as $outlet)
    {
        $email .= "<div style='margin: 5px 0, border: 1px solid #aaa'>";
        $email .= "<h2>" . $outlet['outlet'] . "</h2>";

        $email .= "<h3>Lunch</h3>";

        if (empty($outlet['lunch']))
            $email .= "<p>No menu available for this meal.</p>";

        foreach ($outlet['lunch'] as $meal)
        {
            $email .= "<li>" . $meal['product_name'];

            if ($meal['diet_type'])
                $email .= " <small>(" . $meal['diet_type'] . ")</small>";
           
           $email .= "</li>";
        }// End of foreach

        $email .= "<h3>Dinner</h3>";

        if (empty($outlet['dinner']))
            $email .= "<p>No menu available for this meal.</p>";

        foreach ($outlet['dinner'] as $meal)
        {
            $email .= "<li>" . $meal['product_name'];

            if ($meal['diet_type'])
                $email .= " <small>(" . $meal['diet_type'] . ")</small>";
           
           $email .= "</li>";
        }// End of foreach

        if (!empty($outlet['notes']))
            $email .= "<p style='font-weight: bold; border: 1px solid black; padding: 4px;'>" . $outlet['notes'] . "</p>";
    }// End of foreach

    $email .= "<hr />";
    $email .= "<p><small>To stop receiving these emails, please visit <a href='https://zacharyseguin.ca/projects/you-want-food/#/email-unsubscribe'>https://zacharyseguin.ca/projects/you-want-food/#/email-unsubscribe</a>.</small></p>";

    return $email;
}// End of generateMenuHTML function

// Load the menu information from the API
$menu = json_decode(file_get_contents('https://zacharyseguin.ca/projects/you-want-food/api?data=menu'), true);

// Get only menu information for today
$today = date('Y-m-d');
$today_menu = Array();

$info_found = false;

foreach ($menu as $outlet)
{
    $outlet_menu = Array(
        'outlet' => $outlet['outlet_name']
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

$menu_html = generateMenuHTML($today_menu);

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
