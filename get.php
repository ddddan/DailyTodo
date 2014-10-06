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

// Adjust days out if set in query string.
$days_out = DEFAULT_DAYS_OUT;
$d = filter_input(INPUT_GET, 'd');
if (!empty($d)) {
    $days_out = $d;
}

// TODO: Add the following parameters:
// uid
// allData - not just filtered data
//
// Read user data (if applicable)
$user_info = explode('\\', filter_input(INPUT_SERVER, 'REMOTE_USER'));
$user = end($user_info);

$udata_all = file_get_contents('udata/' . $user);
$udata = json_decode($udata_all, true);

// echo 'udata: <pre>' . print_r($udata, true) . '</pre>'; exit;
// Connect to DB
$db = db_connect() or die("Can't connect to database.");

// Retrieve client list
$clients = array();
$query_clients = 'select pkClientID, ClientName, ClientShortName from tblClients where Active = 1';
try {
    $sth_clients = $db->prepare($query_clients);
    $sth_clients->execute();
    while ($row = $sth_clients->fetch(PDO::FETCH_ASSOC)) {
        $clients[$row['pkClientID']] = $row;
    }
} catch (PDOException $e) {
    echo "\nPDO::errorInfo():\n";
    print_r($db->errorInfo());
}

// Retrieve task list from server
// Currently using the 'spoToDoList' stored procedure
// $filtered_data contains data with some low-urgency tasks filtered out (see includes/defs.php)
// echo '<pre>' . print_r($_SERVER, true) . '</pre>'; exit;

$query = 'exec spoToDoList :user, :daysout';

$data = array();
$filtered_data = array();

$user_columns = array();

try {
    $sth = $db->prepare($query);
    $sth->bindValue(':user', $user);
    $sth->bindValue(':daysout', $days_out);
    $sth->execute();

    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        // Add client name
        $clientName = '';
        if (isset($clients[$row['fkClientID']])) {
            $clientName = $clients[$row['fkClientID']]['ClientShortName'];
        }
        $row['Client'] = $clientName;

        // Add user data
        $taskID = $row['pkCampaignTaskID'];
        if (!empty($udata[$taskID])) {
            foreach ($udata[$taskID] as $prop => $value) {
                $row[$prop] = $value;
                if (!in_array($prop, $user_columns)) {
                    array_push($user_columns, $prop);
                }
            }
        }


        array_push($data, $row);
        if (!in_array($row['TaskName'], $tasks_to_filter)) {
            array_push($filtered_data, $row);
        }
    }
} catch (PDOException $e) {
    echo "\nPDO::errorInfo():\n";
    print_r($db->errorInfo());
}

// Create a separate columns list for easy reference
$columns = array('Client');
$filtered_columns = array('Client');

// echo 'user_columns: <pre>' . print_r($user_columns, true) . '</pre>'; exit;



while (list ($key, $val) = each($data[0])) {
    array_push($columns, $key);
    if (!in_array($key, $columns_to_filter)) {
        array_push($filtered_columns, $key);
    }
}
if (!empty($user_columns)) {
    foreach ($user_columns as $col) {
        array_push($columns, $col);
        if (!in_array($col, $columns_to_filter)) {
            array_push($filtered_columns, $col);
        }
    }
}


// Output the data (for reading by AJAX)

$results_array = array(
    // 'data' => $data,
    'filtered_data' => $filtered_data,
    'columns' => $columns,
    'filtered_columns' => $filtered_columns
);

echo json_encode($results_array);
