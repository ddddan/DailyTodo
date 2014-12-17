<?php

/*
 * includes/functions.php - Auxiliary functions for DailyTodo
 */

require_once('defs.php');

function db_connect() {
    try {
        $dbh = new PDO('sqlsrv:Server=' . DB_SERVER . ';Database=' . DB_MASTER);
        $dbh->query('use ' . DB_NAME);
        return $dbh;
    } catch (PDOException $e) {
        die('Error: Cannot connect to DB: ' . $e->getMessage());
    }
}

/**
 * Return the status of "Past Due", "Due Soon", '' or 'Beyond Radar' based on
 * the date difference between today and the due date
 * 
 * @param type $dueDate
 * @return string
 */
function get_status($dueDate) {
    // Ignore any empty date
    if (empty($dueDate)) {
        return null;
    }
    
    // Convert to DateTimeInterface if needed
    if (!is_a($dueDate, 'DateTimeInterface')) {
        $dueDate = date_create($dueDate);
    }

    $today = date_create();

    $result = '';
    $diff = date_diff($today, $dueDate);
    $days = $diff->format('%r%a');

    if ($days > MAX_DAYS_OUT) { // Filter anything beyond that date
        return 'Beyond Radar';
    } else if ($days < 0) {
        return 'Past Due';
    } else if ($days < 3) {
        return 'Due Soon';
    }

    return $result;
}
