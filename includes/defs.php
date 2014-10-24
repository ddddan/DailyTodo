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

$task_mapping_list = array(
    array('key' => 'DataPlanDue', 'value' => 'Data Plan'),
    array('key' => 'ClientDataDue', 'value' => 'Client Data Due'),
    array('key' => 'SampleDataDue', 'value' => 'IPR'),
    array('key' => 'DataToPrime', 'value' => 'Instructions to Prime'),
    array('key' => 'FinalDataDue', 'value' => 'Final Data Due'),
    array('key' => 'NetCounts', 'value' => 'Net Counts')
    
);