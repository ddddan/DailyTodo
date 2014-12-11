<?php

/**
 * includes/defs.php - Definitions for DailyTodo
 */


define('DB_SERVER', 'BEA001\WAREHOUSE');
define('DB_MASTER', 'master');
define('DB_NAME', 'ProjectManagerSQL');

define('TABLE_TASKS', 'tblCampaignTasks');
define('TABLE_ROLES', 'tblCampaignRoles');
define('TABLE_DOCKETS', 'tblDockets');

define('MAX_DAYS_OUT', 30);

define('SAVE_PATH', 'U:\Projects\dailytodo');
define('SAVE_FILE', 'dailytodo_status.json');


$columns_to_filter = array(
    'TaskID' => 1
);

$task_mapping_list = [
    ['key' => 'DataPlanDue', 'value' => 'Data Plan'],
    ['key' => 'ClientDataDue', 'value' => 'Client Data Due'],
    ['key' => 'SampleDataDue', 'value' => 'IPR'],
    ['key' => 'DataToPrime', 'value' => 'Instructions to Prime'],
    ['key' => 'FinalDataDue', 'value' => 'Final Data Due'],
    ['key' => 'NetCounts', 'value' => 'Net Counts']
    
   ];

$task_types = [
    [],
    ['symbol' => 'D', 'type_name' => 'Data Work', 'in_cp' => true,
        'steps' => null],
    ['symbol' => 'A', 'type_name' => 'Analysis', 'in_cp' => false,
        'steps' => [
            [
                'name' => 'Email Summary Sent',
                'days_out' => -6
                ],
            [
                'name' => '1st Review',
                'days_out' => -5
                ],
            [
                'name' => '2nd Review',
                'days_out' => -3
                ],
            [
                'name' => 'Write-up',
                'days_out' => -1
                ],
            [
                'name' => 'Presentation',
                'days_out' => 0
                ],
        ]],
    ['symbol' => 'R', 'type_name' => 'Results Reporting', 'in_cp' => false,
                'steps' => [
            [
                'name' => 'Import & Update',
                'days_out' => -3
                ],
            [
                'name' => 'Reports',
                'days_out' => -2
                ],
            [
                'name' => 'Response Curves',
                'days_out' => -1
                ],
            [
                'name' => 'Issue Reports',
                'days_out' => 0
                ],
        ]],
    ['symbol' => 'N', 'type_name' => 'Net Counts', 'in_cp' => true,
        'steps' => null],
    ['symbol' => 'I', 'type_name' => 'Insights Work', 'in_cp' => false,
        'steps' => null],
    ['symbol' => 'O', 'type_name' => 'Other', 'in_cp' => false,
        'steps' => null],
];

// Establish the default display order of the columns
$filtered_columns_default = [
    'Type',
    'TaskName',
    'Docket',
    'Client',
    'CampaignName',
    'DueDate',
    'Status',
    'Priority',
    'Notes'
];