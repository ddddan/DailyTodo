<?php

/**
 * includes/defs.php - Definitions for DailyTodo
 */


define('MAX_DAYS_OUT', 30);

define('SAVE_PATH', 'U:\Projects\dailytodo');
define('SAVE_FILE', 'dailytodo_status.json');


$columns_to_filter = array(
    'TaskID' => 1
);

$task_types = [
    [],
    ['symbol' => 'A', 'type_name' => 'Task Type A', 
        'steps' => null],
    ['symbol' => 'B', 'type_name' => 'Task Type A', 
        'steps' => [
            [
                'name' => 'Step 1',
                'days_out' => -6
                ],
            [
                'name' => 'Step 2',
                'days_out' => -5
                ],
            [
                'name' => 'Step 3',
                'days_out' => -3
                ],
            [
                'name' => 'Step 4',
                'days_out' => -1
                ],
            [
                'name' => 'Step 5',
                'days_out' => 0
                ],
        ]],

];

// Establish the default display order of the columns
$filtered_columns_default = [
    'Type',
    'TaskName',
    'SubprojectName',
    'ContactName',
    'ProjectName',
    'DueDate',
    'Status',
    'Priority',
    'Notes'
];