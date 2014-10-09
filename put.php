<?php

/* 
 * put.php - Update the user file with new details
 */

require_once('includes/defs.php');
require_once('includes/functions.php');

date_default_timezone_set('America/Toronto');
error_reporting(E_ALL);

// echo '<pre>' . print_r($_POST) . '</pre>';

// Parse new details
if (!empty($newdetails = filter_input(INPUT_POST, 'newdetails'))) {
    $newdetails = json_decode($newdetails, true);
} else {
    die ("[ERROR] Nothing received");
}

echo 'newdetails: <pre>' . print_r($newdetails, true) . '</pre>';

// Open existing user file and import data
// TODO: Add the following parameters:
// uid
// allData - not just filtered data
//
// Read user data (if applicable)
$user_info = explode('\\', filter_input(INPUT_SERVER, 'REMOTE_USER'));
$user = end($user_info);

$filename = 'udata/' . $user;

$udata_all = file_get_contents($filename);
$udata = json_decode($udata_all, true);

// Update user data with new details
foreach ($newdetails['data'] as $field => $value) {
    $udata[$newdetails['TaskID']][$field] = $value;
}

$udata_string = json_encode($udata);

// Save to file
file_put_contents($filename, $udata_string);

// Return user data 
echo $udata_string;