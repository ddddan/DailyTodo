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

define('DEFAULT_DAYS_OUT', 999);

define('SAVE_PATH', 'U:\Projects\dailytodo');
define('SAVE_FILE', 'dailytodo_status.json');


$tasks_to_filter = array(
    'Start to Prepare Data Plan',
    'Revised Data Plan Ready',
    'Data Initiation Check-in Meeting',
    'Data Request to Client',
    'Lists Ordered',
    'Initial Data Report & Sample files to Client',
    'Initiate List Development with Provider',
    'Initial Data Work Approved',
    'Final Data Work Approved'
);

$columns_to_filter = array(
    'fkClientID',
    'fkCampaignID',
    'pkCampaignTaskID',
    'fkRoleID',
    'UserID',
    'IsMailDate',
    'ScheduledFinish',
    'WorkingFinish',
    'ActualFinish',
    'Client',
    'DaysLate'
);
       
$task_abbr = array(
    'Data Plan Presentation to Client' => 'Data Plan',
    'Initial Data Ready (book check-in if req\'d)' => 'IPR',
    'Data & Instructions to Prime/Advanced (Spec Check-in)' => 'Instructions to Prime',
    'Net Mail Counts to Client & TM Provider' => 'Net Counts',
    'Net Mail Files & Counts to Client' => 'Net Counts',
);    