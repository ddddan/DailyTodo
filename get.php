<?php

/**
 * get.php - Obtain values from DB
 * 
 * uid is assumed to be server uid unless specified
 * 
 */
require_once('includes/defs.php');
require_once('includes/functions.php');


date_default_timezone_set('America/Toronto');
error_reporting(E_ALL);

$data = array(); // Will contain the final data set

$user_info = explode('\\', filter_input(INPUT_SERVER, 'REMOTE_USER'));
$user = end($user_info);

if (empty($user)) {
    $user = DEFAULT_USER;
}

// Check for Raw parameter - will just dump unfiltered results
$dump_raw = filter_input(INPUT_GET, 'dumpraw');
if (!empty($dump_raw) && $dump_raw != 'raw') {
    $dump_raw = null;
}

// Check for debug parameters
$debug = filter_input(INPUT_GET, 'debug');
if (!empty($debug) && $debug != 'dm20150206') {
    $debug = null;
}

$udata_all = file_get_contents('udata/' . $user);
$udata = json_decode($udata_all, true);

// Preserve user tasks (but suppress if completed)
foreach ($udata as $prop => $value) {
    if (isset($value['Type']) && (!isset($value['Completed']) || $value['Completed'] == false)) {
        $keep = $value;
        $keep['TaskID'] = $prop;
        $keep['Status'] = get_status($keep['DueDate']);

        array_push($data, $keep);
        $udata[$prop]['keep'] = true;
    }
}

// echo 'udata: <pre>' . print_r($udata, true) . '</pre>'; exit;
// Connect to DB - Has to be rebuilt if DB used
// $db = db_connect() or die("Can't connect to database.");


// Create a separate columns list for easy reference
$columns = array();

// Using prescriptive filtered columns list for now
$filtered_columns = $filtered_columns_default;

// Update user file (remove inactive jobs)
$udata_keep = array();
foreach ($udata as $prop => $dummy) {
    if (isset($udata[$prop]['keep'])) {
        $udata_keep[$prop] = $udata[$prop];
        unset($udata_keep[$prop]['keep']);
    }
}

$udata_string = json_encode($udata_keep);

// Save to file
file_put_contents('udata/' . $user, $udata_string);



// Output the data (for reading by AJAX)

// TODO: Add Dynamic client and task functionality
$clients = $default_clients;

$results_array = array(
    'data' => $data,
    'columns' => $columns,
    'filtered_columns' => $filtered_columns,
    'clients' => $clients
);

echo json_encode($results_array);
