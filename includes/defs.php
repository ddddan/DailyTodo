<?php

/**
 * includes/defs.php - Definitions for DailyTodo
 */


define('MAX_DAYS_OUT', 30);

define('SAVE_PATH', 'U:\Projects\dailytodo');
define('SAVE_FILE', 'dailytodo_status.json');

define('DEFAULT_USER', 'dmullin');


$columns_to_filter = array(
    'TaskID' => 1
);

$task_types = [
    [],
    ['symbol' => 'W', 'type_name' => 'Web Programming', 
        'steps' => null],
    ['symbol' => 'M', 'type_name' => 'Music Production', 
        'steps' => null],
    ['symbol' => 'R', 'type_name' => 'Research',
        'steps' => null],
    ['symbol' => 'H', 'type_name' => 'Home-related',
        'steps' => null],
    ['symbol' => 'D', 'type_name' => 'Documentation',
        'steps' => null],
    ['symbol' => 'V', 'type_name' => 'Video Production',
        'steps' => null],
    ['symbol' => 'C', 'type_name' => 'Communication',
        'steps' => null],
    ['symbol' => 'O', 'type_name' => 'Organizational',
        'steps' => null]
];

$default_clients = [
    ['ClientShortName' => 'IPG', ClientName => 'IPG'],
    ['ClientShortName' => 'Home', ClientName => 'Home'],
    ['ClientShortName' => 'Hopeward', ClientName => 'Hopeward'],
    ['ClientShortName' => 'RoatanAlive', ClientName => 'Roatan Alive'],
    ['ClientShortName' => 'WBC', ClientName => 'WBC'],
    ['ClientShortName' => 'Blakely', ClientName => 'Blakely'],
    ['ClientShortName' => 'OtherWork', ClientName => 'Other Work']
    
];

// Establish the default display order of the columns
$filtered_columns_default = [
    'Type',
    'Task',
    'Subproject',
    'Client',
    'Project',
    'DueDate',
    'Status',
    'Priority',
    'Notes'
];