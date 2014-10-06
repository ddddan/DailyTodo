<script type="text/javascript">
/** 
 * dailytodo.js.php - contains globals for JS and allows passing data from PHP to JS
 */

window.sitescriptdata = {
    taskData : {},
    taskfData : {},
    taskCols : {},
    taskfCols : {},
    taskAbbr : <?php echo json_encode($task_abbr); ?>,
    SAVE_PATH: '<?php echo SAVE_PATH; ?>',
    SAVE_FILE: '<?php echo SAVE_FILE; ?>',
    sortKeys: [
        'DueDate', 'TaskName', 'Client', 'CampaignName'
    ],
    currSortKey: 'DueDate',
    detailChanged: false,
    inputFields: [
        'priority', 'notes'
    ],
    detailFields: {},
    newDetails: {}
};

</script>