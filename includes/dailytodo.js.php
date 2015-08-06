<script type="text/javascript">
/** 
 * dailytodo.js.php - contains globals for JS and allows passing data from PHP to JS
 */

window.sitescriptdata = {
    debug : <?php echo ($debug ? 'true' : 'false'); ?>,
    taskTypes : <?php echo json_encode($task_types); ?>,
    lastUTaskID : 0,
    clients: {},
    taskData : {},
    taskCols : {},
    taskfCols : {},
    SAVE_PATH: '<?php echo SAVE_PATH; ?>',
    SAVE_FILE: '<?php echo SAVE_FILE; ?>',
    sortKeys: [
        'DueDate', 'Type', 'Docket', 'TaskName', 'Client', 'CampaignName', 'Priority'
    ],
    currSortKey: '',
    defaultSortKey: 'DueDate',
    priorityList: [],
    
    popupActive: false,
    refreshTimer: null,
    // Detail edit
    detailChanged: false,
    inputFields: [
        'priority', 'notes'
    ],
    detailFields: {},
    taskUpdate: {}
};

</script>