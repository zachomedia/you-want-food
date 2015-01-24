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
    
require '../config.php';
header('Content-Type: application/json');
    
// If data directories do not exists, create them.
if (!is_dir($config['DATA_DIRECTORY'])) { mkdir($config['DATA_DIRECTORY']); }
if (!is_dir($config['OUTLETS_DATA_DIRECTORY'])) { mkdir($config['OUTLETS_DATA_DIRECTORY']); }
if (!is_dir($config['MENU_DATA_DIRECTORY'])) { mkdir($config['MENU_DATA_DIRECTORY']); }

// Additional parameters for processing the request
$date = date('YmdH') . (floor((int)date('i') / 30));
$filename = "$date.json";

if (empty($_GET['data']) && empty($_GET['action'])) {
    header('HTTP/1.0 400 Bad Request');
    die();
}

$request = "";
if (!empty($_GET['data'])) {
    $request = $_GET['data'];
} else if (!empty($_GET['action'])) {
    $request = $_GET['action'];
}

if ($request == 'outlets') {
    $filename = $config['OUTLETS_DATA_DIRECTORY'] . $filename;
    
    // Load the information if it wasn't loaded today.
    if (!file_exists($filename)) {
        file_put_contents(  $filename,
                            file_get_contents($config['API_BASE_URL'] . '/foodservices/locations.json?key=' . $config['API_KEY']));
    }// End of if
    
    // Return the information
    $outlets = json_decode(file_get_contents($filename), true);
    
    if ($outlets['meta']['status'] == 200) {
        echo json_encode($outlets['data']);
    } else {
        echo json_encode(array());
    }
} else if ($request == 'menu') {
    $filename = $config['MENU_DATA_DIRECTORY'] . $filename;
    
    // Load the information if it wasn't loaded today.
    if (!file_exists($filename)) {
        file_put_contents(  $filename,
                            file_get_contents($config['API_BASE_URL'] . '/foodservices/menu.json?key=' . $config['API_KEY']));
    }// End of if
    
    // Return the information
    $menu = json_decode(file_get_contents($filename), true);
    
    if ($menu['meta']['status'] == 200) {
        if (empty($_GET['outlet_id'])) {
            echo json_encode($menu['data']['outlets']);
        } else {
            $found = false;
            foreach ($menu['data']['outlets'] as $outlet) {
                if ($outlet['outlet_id'] == $_GET['outlet_id']) {
                    echo json_encode($outlet);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) { echo json_encode(null); }
        }
    } else {
        echo json_encode(array());
    }
} else if ($request == 'email_subscribe') {
    $form_data = json_decode(file_get_contents("php://input"), true);
    $email = $form_data['email'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('success' => false, 'error' => 'The email address provided is invalid.'));
    } else {
        try {
            $conn = new PDO('mysql:host=' . $config['DATABASE_HOSTNAME'] . ';dbname=' . $config['DATABASE_DATABASE'], $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('SELECT * FROM email_subscriptions WHERE email = :email');
            $stmt->execute(array('email' => $email));

            $results = $stmt->fetchAll();
            if (count($results) != 0) {
                if ($results[0]['enabled'] == '0') {
                    $stmt = $conn->prepare('UPDATE email_subscriptions SET enabled = 1 WHERE email = :email');
                    $stmt->execute(array('email' => $email));

                    echo json_encode(array('success' => $stmt->rowCount() == 1));
                } else {
                    echo json_encode(array('success' => false, 'error' => 'The email address provided has already subscribed to receive emails.'));
                }// End of else
            } else {
                $stmt = $conn->prepare('INSERT INTO email_subscriptions (email, enabled) VALUES (:email, 1)');
                $stmt->execute(array('email' => $email));

                echo json_encode(array('success' => $stmt->rowCount() == 1));
            }
        } catch (PDOException $e) {
            echo json_encode(array( 'success' => false, 'error' => 'An unknown error occured while subscribing.'));
        }
    }
} else if ($request == 'email_unsubscribe') {
    $form_data = json_decode(file_get_contents("php://input"), true);
    $email = $form_data['email'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('success' => false, 'error' => 'The email address provided is invalid.'));
    } else {
        try {
            $conn = new PDO('mysql:host=' . $config['DATABASE_HOSTNAME'] . ';dbname=' . $config['DATABASE_DATABASE'], $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('SELECT * FROM email_subscriptions WHERE email = :email AND enabled=1');
            $stmt->execute(array('email' => $email));

            $results = $stmt->fetchAll();
            if (count($results) != 1) {
                echo json_encode(array('success' => false, 'error' => 'The email address provided has not subscribed to receive emails.'));
            } else {
                $stmt = $conn->prepare('UPDATE email_subscriptions SET enabled = 0 WHERE email = :email');
                $stmt->execute(array('email' => $email));

                echo json_encode(array('success' => $stmt->rowCount() == 1));
            }
        } catch (PDOException $e) {
            echo json_encode(array( 'success' => false, 'error' => 'An unknown error occured while unsubscribing'));
        }
    }    
} else {
    header('HTTP/1.0 400 Bad Request');
    die();
}

?>
