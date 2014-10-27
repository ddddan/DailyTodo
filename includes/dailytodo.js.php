<script type="text/javascript">
/** 
 * dailytodo.js.php - contains globals for JS and allows passing data from PHP to JS
 */

window.sitescriptdata = {
    debug : <?php echo ($debug ? 'true' : 'false'); ?>,
    taskData : {},
    taskCols : {},
    taskfCols : {},
    SAVE_PATH: '<?php echo SAVE_PATH; ?>',
    SAVE_FILE: '<?php echo SAVE_FILE; ?>',
    sortKeys: [
        'DueDate', 'Docket', 'TaskName', 'Client', 'CampaignName', 'Priority'
    ],
    currSortKey: 'DueDate',
    priorityList: [],
    
    // Detail edit
    detailChanged: false,
    inputFields: [
        'priority', 'notes'
    ],
    detailFields: {},
    newDetails: {}
};

</script>