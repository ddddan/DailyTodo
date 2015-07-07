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
    echo 'newdetails: <pre>' . print_r($newdetails, true) . '</pre>';
} else if (!empty($newtask = filter_input(INPUT_POST, 'newtask'))) {
    $newtask = json_decode($newtask, true);
    echo 'newtask: <pre>' . print_r($newdetails, true) . '</pre>';
} else {
    die("[ERROR] Nothing received");
}



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

// If a new task, determine last user task ID and increment
$newTaskID = date('Y') * 1000 + 1;
if (!empty($newtask)) {
    foreach ($udata as $taskID => $content) {
        $tID = intval($taskID);
        if (isset($content['Type']) && $tID >= $newTaskID) {
            echo 'Content found, $tID = ' . $tID . "\n";
            $newTaskID = $tID + 1;
        }
    }

    echo 'newTaskID = ' . $newTaskID . "<br>\n";

    foreach ($newtask['data'] as $field => $value) {
        $udata[$newTaskID][$field] = $value;
    }
} else {
    // Update exisiting task with new details
    if (!isset($newdetails['data'])) {
        print_r($newdetails); exit;
    }
    foreach ($newdetails['data'] as $field => $value) {
        $udata[$newdetails['TaskID']][$field] = $value;
    }
}

$udata_string = json_encode($udata);

// Save to file
file_put_contents($filename, $udata_string);

// Return user data 
echo $udata_string;
