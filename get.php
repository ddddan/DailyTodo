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
// TODO: Add the following parameters:
// uid
// allData - not just filtered data
//
// Read user data (if applicable)
$user_info = explode('\\', filter_input(INPUT_SERVER, 'REMOTE_USER'));
$user = end($user_info);

// Check for Raw parameter - will just dump unfiltered results
$dump_raw = filter_input(INPUT_GET, 'dumpraw');
if (!empty($dump_raw) && $dump_raw != 'raw') {
    $dump_raw = null;
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
// Connect to DB
$db = db_connect() or die("Can't connect to database.");

// Retrieve client list
$clients = array();
$query_clients = 'select pkClientID, ClientName, ClientShortName from tblClients where TimeFoxLookup is not null';
try {
    $sth_clients = $db->prepare($query_clients);
    $sth_clients->execute();
    while ($row = $sth_clients->fetch(PDO::FETCH_ASSOC)) {
        $clients[$row['pkClientID']] = $row;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "\nPDO::errorInfo():\n";
    print_r($db->errorInfo());
}

// Retrieve task list from server
// $filtered_data contains data with some low-urgency tasks filtered out (see includes/defs.php)
// echo '<pre>' . print_r($_SERVER, true) . '</pre>'; exit;

$query = 'exec spoDashboard :user';



$user_columns = array();

try {
    $sth = $db->prepare($query);
    $sth->bindValue(':user', $user);
    $sth->execute();

    // If $dump_raw is set, just echo the raw output
    if ($dump_raw) {
        header('Content-Type: text/plain');
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
            exit;
        }
        exit;
    }

    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {

// Add client name
        $clientName = '';
        if (isset($clients[$row['fkClientID']])) {
            $clientName = $clients[$row['fkClientID']]['ClientShortName'];
        }

// Map the tasks listed in $task_mapping into rows of the new array
        foreach ($task_mapping_list as $map) {
            $keep = array();
            // Skip completed tasks
            extract($map);

            if ($row[$key . '_Comp'] !== '0') {
                continue;
            }

            $taskID = $row[$key . '_TaskID'];

            // Set task type symbol
            // See defs.php for sequence
            $keep['Type'] = ($value == 'Net Counts' ? 4 : 1);

            $keep['Docket'] = $row['pkDocketNum'];
            $keep['TaskID'] = $taskID;
            $keep['Client'] = $clientName;
            $keep['CampaignName'] = $row['Project'];
            $keep['TaskName'] = $value;

            // Add user data
            if (!empty($udata[$taskID])) {
                foreach ($udata[$taskID] as $prop => $value) {
                    if ($prop == 'keep') {
                        continue;
                    }
                    $keep[$prop] = $value;
                    if (!in_array($prop, $user_columns)) {
                        array_push($user_columns, $prop);
                    }
                }
                $udata[$taskID]['keep'] = true;
            }

            // Status - based on relationship to today's date;
            $dueDate = date_create($row[$key]);
            $keep['DueDate'] = $dueDate->format('Y-m-d');

            $keep['Status'] = get_status($dueDate);
            // Suppress any tasks beyond radar
            if ($keep['Status'] === 'Beyond Radar') {
                break;
            }

            array_push($data, $keep);

            // TEST: Only show the first outstanding task of a given campaign
            // TODO: Re-sort by date (keep only earliest)
            break;
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "\nPDO::errorInfo():\n";
    print_r($db->errorInfo());
}

// Create a separate columns list for easy reference
$columns = array();

// Using prescriptive filtered columns list for now
$filtered_columns = $filtered_columns_default;

/*
  $filtered_columns = array();

  // echo 'user_columns: <pre>' . print_r($user_columns, true) . '</pre>'; exit;



  while (list ($col, $val) = each($data[0])) {
  array_push($columns, $col);
  if (!isset($columns_to_filter[$col])) {
  array_push($filtered_columns, $col);
  }
  }

  if (!empty($user_columns)) {
  foreach ($user_columns as $col) {
  array_push($columns, $col);
  if (!isset($columns_to_filter[$col]) && !in_array($col, $filtered_columns)) {
  array_push($filtered_columns, $col);
  }
  }
  }
 */
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

$results_array = array(
    'clients' => $clients,
    'data' => $data,
    'columns' => $columns,
    'filtered_columns' => $filtered_columns
);

echo json_encode($results_array);
