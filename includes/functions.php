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
