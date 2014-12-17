/** 
 * dailytodo.js - Main JS file for dailytodo
 */

/**
 * sortByKey() - sort a JS object by key
 * 
 * @param {type} array : the array to sort
 * @param {type} key : the key to sort by
 * @param {type} sortDesc : sort descending
 * @returns {unresolved} : the sorted array
 */
function sortByKey(array, key, sortDesc) {
    return array.sort(function (a, b) {
        var x = (sortDesc ? b[key] : a[key]);
        var y = (sortDesc ? a[key] : b[key]);
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}

/**
 * AddDynamic() - Populates dynamic fields
 * 
 * @returns {undefined}
 */
function AddDynamic() {
    var ws = window.sitescriptdata;
    // Task types
    var e = document.getElementById('newtask_type');
    for (var i = 1; i < ws.taskTypes.length; i++) {
        if (!!ws.taskTypes[i].in_cp) {
            continue;
        }
        var eOption = document.createElement('option');
        eOption.setAttribute('value', i);
        eOption.textContent = ws.taskTypes[i].type_name;
        e.appendChild(eOption);
    }
    // Client names
    // Create list
    var clientList = [{value: 'Internal', name: 'Blakely House'}]; // Default is internal
    for (i in ws.clients) {
        clientList.push({value: ws.clients[i].ClientShortName, name: ws.clients[i].ClientName});
    }
    clientList = clientList.deepSortAlpha.apply(clientList, ['name']);
    e = document.getElementById('newtask_client');
    for (i = 0; i < clientList.length; i++) {
        var eOption = document.createElement('option');
        eOption.value = clientList[i].value;
        eOption.textContent = clientList[i].name;
        e.appendChild(eOption);
    }

}

/**
 * LoadResults() - Load the results via ajax
 * 
 */
function LoadResults() {
    var ws = window.sitescriptdata;

    // Show loading spinner
    document.getElementById('loading').removeClassName('hidden');

    // AJAX request to load the data 
    var xmlhttp = new XMLHttpRequest;
    xmlhttp.onreadystatechange = function () {
        if (this.readyState === 4) {
            // Hide spinner
            document.getElementById('loading').addClassName('hidden');

            // Handle errors
            if (this.status !== 200) {
                debugLog(this.statusText);
                alert('ERROR: Unable to update the results table.  Please contact technical support');
                return;
            }

            // Read data
            var data = JSON.parse(this.response);

            ws.taskData = data.data;
            ws.taskfCols = data.filtered_columns;
            ws.taskCols = data.columns;
            ws.clients = data.clients;

            // Set lastUTaskID - the last user task id to increment when creating tasks
            for (var i = 0; i < ws.taskData.length; i++) {
                if (!ws.taskTypes[ws.taskData[i].Type].in_cp && ws.taskData[i].TaskID > ws.lastUTaskID) {
                    ws.lastUTaskID = ws.taskData[i].TaskID;
                }
            }

            // Populate dynamic options (e.g. task types)
            AddDynamic();

            // Display table
            DisplayMasterTable('DueDate');

        }
    };

    var AJAXrequest = 'get.php';

    xmlhttp.open('GET', AJAXrequest);
    xmlhttp.send();

}

/**
 * saveTask() - Save task details (user-defined) via ajax
 *  
 * @returns {undefined}
 */

function saveTask() {
    var ws = window.sitescriptdata;
    // Ensure an update has been triggered
    if (!ws.taskUpdate.type) {
        return;
    }

    // Show spinner
    document.getElementById('submitting').removeClassName('hidden');

    // AJAX request to load the data 
    var xmlhttp = new XMLHttpRequest;

    xmlhttp.open('POST', 'put.php', false);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    if (ws.taskUpdate.type === 'update') {
        xmlhttp.send('newdetails=' + JSON.stringify(ws.taskUpdate));
    } else if (ws.taskUpdate.type === 'newtask') {
        xmlhttp.send('newtask=' + JSON.stringify(ws.taskUpdate));
    } else {
        alert('ERROR: saveTask(): Invalid request.  Please contact technical support');
        return;
    }

    // Failure
    if (xmlhttp.status !== 200) {
        debugLog(this.statusText);
        alert('ERROR: Unable to update the details file.  Please contact technical support');
        return;
    }
    // Success

    // Save details to local storage
    var row;
    if (ws.taskData.type === 'update') {
        row = document.getElementById('detail').getAttribute('data-rowid');
    } else {
        row = ws.taskData.length;
        ws.taskData[row] = {};
    }
    for (var field in ws.taskUpdate.data) {
        ws.taskData[row][field] = ws.taskUpdate.data[field];
    }


    // Update priorities if applicable
    if (!!ws.taskUpdate.data['Priority']) {
        GetPriorities();
    }

    // Hide spinner
    document.getElementById('submitting').addClassName('hidden');



    // Refresh
    DisplayMasterTable();

    ws.detailChanged = false;
    ws.taskUpdate = {};

}

/**
 * GetPriorities() - Examine the task data to establish the list of priorities.
 * 
 * @returns {undefined}
 */
function GetPriorities() {
    var priorityHash = [];
    var tData = window.sitescriptdata.taskData;
    // Read in priorities from task data
    for (var i = 0; i < tData.length; i++) {
        // Check for existence of priority field for this record
        if (!tData[i].hasOwnProperty('Priority') || !tData[i].Priority) {
            continue;
        }
        // Check for existence in priorityHash already 
        var p = tData[i].Priority;
        if (!priorityHash[p * 100])
        {
            // Use multiple of 100 to help avoid collisions
            priorityHash[p * 100] = i;
        } else {
            // Try to use due date as a tiebreaker; if not, use fifo
            // TODO: Add 3+ way tiebreaking
            var oldTask = priorityHash[p * 100];
            if (tData[oldTask].DueDate > tData[i].DueDate) {
                priorityHash[p * 100 + 1] = oldTask;
                priorityHash[p * 100] = i;
            } else {
                priorityHash[p * 100 + 1] = i;
            }
        }
    }
    // Set priority order and re-assign priorities to tasks
    var priorityList = [];
    p = 1;
    priorityHash.forEach(function (element, index, array) {
        tData[element].Priority = p.toString();
        priorityList[p++] = element;
    });

}

/**
 * DisplayMasterTable() - Display the master table (raw data)
 * 
 * Note: By default the sort order will follow window.sitescriptdata.defaultSortOrder
 * 
 * @param {type} sortKey : which key to sort by (if null, use default given)
 * @param {type} sortDesc : sort descending (if null or false, use ascending)
 * @param {type} allData : use unfiltered data (if null or false, use filtered)
 * @returns {undefined} : no return value
 */
function DisplayMasterTable(sortKey, sortDesc, allData) {
    var ws = window.sitescriptdata;

    // Establish sort keys (place <sortKey> first in the sort order, 
    // and then others in default order
    var sortOrder = [];
    if (!!sortKey) {
        sortOrder = [sortKey];
    } else if (!!ws.currSortKey) {
        sortKey = ws.currSortKey;
        sortOrder = [ws.currSortKey];
    }
    for (var i = 0; i < ws.sortKeys.length; i++) {
        if (ws.sortKeys[i] !== sortKey) {
            sortOrder.push(ws.sortKeys[i]);
        }
    }

    if (ws.debug) {
        // document.getElementById('debug').innerHTML = '<pre>' + JSON.stringify(sortOrder) + '</pre>'; 
        //document.getElementById('debug').innerHTML = '<pre>' + JSON.stringify(ws.taskData) + '</pre>';
    }

    // Update priorities
    GetPriorities();

    // Create counts object for report
    var counts = {
        pastDue: {
            mine: 0,
            total: 0
        },
        dueSoon: {
            mine: 0,
            total: 0
        },
        onRadar: {
            mine: 0,
            total: 0
        }
    };


    // If table element does not exist, create it; otherwise clear it out.    
    var eContent = document.getElementById('maintable');

    var eTable = document.getElementById('master');
    if (!eTable) {
        eTable = document.createElement('table');
        eTable.id = 'master';
    } else {
        while (!!eTable.firstChild) {
            eTable.removeChild(eTable.firstChild);
        }
    }

    // Create table header (column headings)
    var eTHead = document.createElement('thead');
    var eTRow = document.createElement('tr');

    // See above for explanation
    var data = (allData ? ws.taskData : ws.taskData);
    if (!!sortKey) {
        data = data.deepSortAlpha.apply(data, sortOrder);
    }

    for (i = 0; i < ws.taskfCols.length; i++) {
        var eTH = document.createElement('th');
        eTH.id = 'th_' + ws.taskfCols[i];
        eTH.textContent = ws.taskfCols[i];
        if (sortOrder.indexOf(ws.taskfCols[i]) !== -1) {
            eTH.setAttribute('class', 'colHeader');
            eTH.addEventListener('click', cbColumnHeader);
        }
        eTRow.appendChild(eTH);
    }

    eTHead.appendChild(eTRow);
    eTable.appendChild(eTHead);

    // Create table body (data)
    var eTBody = document.createElement('tbody');
    var sortGroup = "!!!";
    for (i = 0; i < data.length; i++) {
        // Suppress if completed
        if (!!data[i].Completed) {
            continue;
        }
                
        eTRow = document.createElement('tr');
        eTRow.className = 'task ' + (i % 2 ? 'tr_even' : 'tr_odd');

        if (data[i][sortKey] !== sortGroup) {
            sortGroup = data[i][sortKey];
            eTRow.className += ' newGroup';
        }

        for (var j = 0; j < ws.taskfCols.length; j++) {
            var eTD = document.createElement('td');
            // Clean up dates
            var val = data[i][ws.taskfCols[j]];
            if (!!val && !isNumeric(val)) {
                val = val.replace(/ 00:00:00\.000/, '');
            }

            // Use task abbreviations
            data[i][ws.taskfCols[j]] = val;


            // Formatting replacements (but do not update data)

            if (!!val && !isNumeric(val)) {
                val = val.replace(/\n/g, '<br>').replace(/(\<br\>|^)\*/g, '$1&bull;');
                val = val.replace(/\(\(/, '<span class="outside">(').replace(/\)\)/, ')</span>');
            }

            // Replace Type with Type Symbol
            if (ws.taskfCols[j] === 'Type') {
                val = ws.taskTypes[val].symbol;
            }


            // Colouring for status
            if (ws.taskfCols[j] === 'Status') {
                // Flag tasks which are outside my control
                var outside = false;
                if (!!data[i].Notes && data[i].Notes.match(/\(\(/)) {
                    outside = true;
                }
                if (val === 'Past Due') {
                    eTD.addClassName('status_late');
                    counts.pastDue.total++;
                    if (!outside) {
                        counts.pastDue.mine++;
                    }

                } else if (val === 'Due Soon') {
                    eTD.addClassName('status_soon');
                    counts.dueSoon.total++;
                    if (!outside) {
                        counts.dueSoon.mine++;
                    }
                } else {
                    counts.onRadar.total++;
                    if (!outside) {
                        counts.onRadar.mine++;
                    }
                }
            }

            if (!!val) {
                eTD.innerHTML = val;
            }
            eTRow.appendChild(eTD);
        }
        eTBody.appendChild(eTRow);
        // Add event listener to allow clicking on item to add detail
        eTRow.id = 'data_' + i;
        eTRow.addEventListener('click', cbDetail);

    }

    eTable.appendChild(eTBody);
    eContent.appendChild(eTable);

    DisplayCounts(counts);
}

/** 
 * DisplayCounts(counts) - Display the total by Status
 * 
 * @param {type} counts - counts object created by DisplayMasterTable()
 * @returns {undefined}
 */
function DisplayCounts(counts) {
    var eCounts = document.getElementById('counts');

    var total = {
        mine: 0,
        total: 0
    };

    for (var prop in counts) {
        for (var owner in total) {
            var e = document.getElementById(prop + '_' + owner);
            e.textContent = counts[prop][owner];
            total[owner] += counts[prop][owner];
        }
    }

    for (var owner in total) {
        document.getElementById('totalCount_' + owner).textContent = total[owner];
    }
}
/**
 * DisplayAddTaskError(errors) - Display any validation errors in adding tasks
 * 
 * @param {type} errors - array of errors to display
 * @returns {undefined}
 */
function DisplayAddTaskErrors(errors) {
    var eMsg = document.getElementById('addtask_message');

    var eInst = document.createElement('p');
    eInst.textContent = 'Please correct the following errors:';
    var eList = document.createElement('ul');

    errors.map(function (err) {
        var eErr = document.createElement('li');
        eErr.textContent = err;
        eList.appendChild(eErr);
    });
    eMsg.appendChild(eInst);
    eMsg.appendChild(eList);
    eMsg.addClassName('error');
}

/**
 * ClearTaskMessage(msg) - clears anything in the 'addtask_message' div and 
 * replaces it with <msg> if defined
 * 
 * @param {type} msg - any message to display
 * @returns {undefined}
 */
function ClearTaskMessage(msg) {
    var eMsg = document.getElementById('addtask_message');
    while (eMsg.firstChild) {
        eMsg.removeChild(eMsg.firstChild);
    }
    if (!!msg) {
        var ePara = document.createElement('p');
        ePara.textContent = msg;
        eMsg.appendChild(ePara);
    }
    eMsg.removeClassName('error');
}


// CALLBACKS
function cbColumnHeader(evt) {
    var col = evt.target.id.substring(3, 999);
    DisplayMasterTable(col);
    // SaveStatus();
}

/**
 * cbDetail() - Callback for Detailed item view, displayed when user clicks on item
 * 
 * @param {type} evt - The target event
 * @returns {undefined}
 */
function cbDetail(evt) {
    var id = evt.currentTarget.id;
    var row = id.replace(/data_/, '');
    var ws = window.sitescriptdata;
    var data = ws.taskData[row];

    // Set popup active to prevent refresh
    ws.popupActive = true;
    window.clearInterval(ws.refreshTimer);

    // Reset changed status
    ws.detailChanged = false;

    // Display shaded background (lightbox effect)
    document.getElementById('shade').removeClassName('hidden');

    // Populate detail box
    var eDetail = document.getElementById('detail');
    // User defined attributes to help with tracking the request
    eDetail.setAttribute('data-TaskID', data.TaskID);
    eDetail.setAttribute('data-rowid', row);

    var eHead = document.getElementById('detail_header');
    var name = (data.CampaignName !== '-' ? data.CampaignName : data.Client);
    eHead.textContent = name + ' - ' + data.TaskName;

    // Add due date
    document.getElementById('due_date_value').textContent = data.DueDate;

    // Add status
    var eStatusValue = document.getElementById('status_value');
    eStatusValue.textContent = data.Status;
    if (data.Status === 'Due Soon') {
        eStatusValue.addClassName('status_soon');
    } else if (data.Status === 'Past Due') {
        eStatusValue.addClassName('status_late');
    }

    // Add Priority
    var ePriority = document.getElementById('priority');
    ePriority.value = data.Priority || '';
    ePriority.addEventListener('change', cbDetailChanged);

    // Hide Priority Adjust
    var ePriorityAdjust = document.getElementById('detail_priority_adjust');
    ePriorityAdjust.addClassName('hidden');
    ePriorityAdjust.addEventListener('change', cbDetailChanged);

    // Add Notes
    var eNotes = document.getElementById('notes');
    eNotes.value = data.Notes || '';
    eNotes.addEventListener('change', cbDetailChanged);

    // Add Completed for user tasks
    var eCompleted = document.getElementById('detail_completed');
    if (data.Type != 1 && data.Type != 4) {
        if (!!data.Completed) {
            eCompleted.checked = true;
        }
        eCompleted.removeClassName('hidden');
        eCompleted.addEventListener('change', cbDetailChanged);
    } else {
        eCompleted.addClassName('hidden');
    }

    // Display detail box
    eDetail.removeClassName('hidden');

    // Allow closing of detail box using X or clicking background
    document.getElementById('detail_close').addEventListener('click', cbCloseDetail);
    document.getElementById('shade').addEventListener('click', cbCloseDetail);

}

function cbCloseDetail() {
    var ws = window.sitescriptdata;
    // Hide element and background
    var eDetail = document.getElementById('detail');
    eDetail.addClassName('hidden');
    document.getElementById('shade').addClassName('hidden');

    // Blank out input fields
    for (var i = 0; i < ws.inputFields.length; i++) {
        document.getElementById(ws.inputFields[i]).value = '';
    }

    // TODO: Display pinwheel
    if (!!ws.detailChanged) {
        ws.taskData.type = 'update';
        saveTask();
    }

    // Clear popup active to allow refresh and reset timer
    ws.popupActive = false;
    window.clearInterval(ws.refreshTimer);
    ws.refreshTimer = window.setInterval(cbRefresh, 300000);

}


/**
 * cbInputPriority - set priority to 1 on click, if not already set
 * 
 * @param {type} evt
 * @returns {undefined}
 */
function cbInputPriority(evt) {
    var e = evt.currentTarget;
    if (!e.value) {
        //    e.value = 1;
    }
}

/**
 * cbDetailChanged - set changed flag to true to allow saving / cancelling
 * @param {type} evt - The event changed -- to allow updating the new details
 * @returns {undefined}
 */
function cbDetailChanged(evt) {
    var e = evt.target;
    var ws = window.sitescriptdata;
    ws.detailChanged = true;

    // Update task id
    ws.taskUpdate.TaskID = document.getElementById('detail').getAttribute('data-TaskID');

    e.value;
    if (!ws.taskUpdate.data) {
        ws.taskUpdate.type = 'update';
        ws.taskUpdate.data = {};
    }
    // Handle Completed checkbox
    if (e.id === 'completed') {
        ws.taskUpdate.data[e.getAttribute('name')] = !!e.checked;
    } else {
        ws.taskUpdate.data[e.getAttribute('name')] = e.value;
    }

// Handle priority adjustment
    if (e.id === 'priority') {
        // Show the adjust priorities checkbox
        document.getElementById('priority_adjust').setAttribute('checked', true);
        // document.getElementById('detail_priority_adjust').removeClassName('hidden'); 
    }

}
/**
 * cbAddTask - Allow user to add a User Task
 * 
 * @returns {undefined}
 */
function cbAddTask() {
    var ws = window.sitescriptdata;

    // Set popup active flag to prevent refresh
    ws.popupActive = true;
    window.clearInterval(ws.refreshTimer);

    // Show popup
    document.getElementById('addtask').removeClassName('hidden');

    // Display shaded background (lightbox effect)
    document.getElementById('shade').removeClassName('hidden');

    // TODO: Add polyfill for date picker (FF, IE)

    // Set callbacks
    document.getElementById('addtask_submit').addEventListener('click', cbSubmitAddTask);
    document.getElementById('addtask_cancel').addEventListener('click', cbCloseAddTask);
    document.getElementById('addtask_close').addEventListener('click', cbCloseAddTask);
}

/**
 * 
 * cbSubmitAddTask - Attempt to submit the new task
 * 
 * @returns {undefined}
 */
function cbSubmitAddTask() {
    var ws = window.sitescriptdata;

    ws.taskUpdate = {};
    ws.taskUpdate.data = {};
    ws.taskUpdate.type = 'newtask';

    // Remove any existing message or errors
    ClearTaskMessage();

    // Validation
    var errorList = [];
    // Required fields: tasktype
    if (document.getElementById('newtask_type').value === '-1') {
        errorList.push('Please select a task type');
    }
    // Required fields: name
    if (!document.getElementById('newtask_taskName').value) {
        errorList.push('Please enter a task name');
    }

    // Required fields: client
    if (document.getElementById('newtask_client').value === '-1') {
        errorList.push('Please select a client name (use Blakely House for internal work)');
    }

    // Required fields: duedate
    if (!document.getElementById('newtask_dueDate').value) {
        errorList.push('Please select a due date');
    }

    // If necessary, display errors (and halt submission
    if (!!errorList.length) {
        DisplayAddTaskErrors(errorList);
        return; // Not submitted
    }

    // Calculate status
    var today = new Date();
    var dueDate = new Date(document.getElementById('newtask_dueDate').value);
    var timeDiff = dueDate - today;
    var dateDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    var dueSoonLimit = (Math.floor(today.getDay() / 4) + 1) * 2; // Adjusted for weekends
    if (dateDiff < 0) {
        ws.taskUpdate.data.Status = 'Past Due';
    } else if (dateDiff < dueSoonLimit) {
        ws.taskUpdate.data.Status = 'Due Soon';
    } else {
        ws.taskUpdate.data.Status = '';
    }

    // Temp hack: CampaignName is blank
    ws.taskUpdate.data.CampaignName = '-';

    // Update object containing data
    var fields = document.getElementsByClassName('newtask_value');
    [].forEach.call(fields, function (el) {
        var key = el.id.replace(/newtask_/, '')
        key = key.charAt(0).toUpperCase() + key.slice(1); // Uppercase first value of fieldname
        ws.taskUpdate.data[key] = el.value;
    });

    // Show spinner
    document.getElementById('submitting').removeClassName('hidden');

    // Submit to put.php
    saveTask();

    // Close window
    cbCloseAddTask();

    // Hide spinner
    document.getElementById('submitting').addClassName('hidden');


}

/**
 * 
 * cbCloseAddTask - Close the Add Task popup
 * 
 * @returns {undefined}
 */
function cbCloseAddTask() {
    var ws = window.sitescriptdata;

    // Hide popup
    document.getElementById('addtask').addClassName('hidden');

    // Remove shade / lightbox effect
    document.getElementById('shade').addClassName('hidden');

    // Clear popup active flag to allow refresh
    ws.popupActive = false;
    window.clearInterval(ws.refreshTimer);

    // Clear fields
    // Clear selects
    document.getElementById('newtask_type').value = -1;
    document.getElementById('newtask_client').value = -1;

    // Clear inputs (iteratively)
    var inputs = ['taskName', 'dueDate', 'docket', 'client', 'priority', 'notes'];
    inputs.map(function (name) {
        document.getElementById('newtask_' + name).value = '';
    });

    //Clear notes
    // document.getElementById('newtask_notes').textContent = '';

    // Clear errors and reset the message div
    ClearTaskMessage('Enter your task details below.');

}

/**
 * cbRefresh - refresh the screen
 * 
 * @returns {undefined}
 */
function cbRefresh() {
    if (!window.sitescriptdata.popupActive) {
        location.reload();
    }
}



window.onload = function () {
    var ws = window.sitescriptdata;
    if (!!ws.debug) {
        //    document.getElementById('debug').innerHTML = '<pre>' + JSON.stringify(ws.taskTypes, undefined, 4) + '</pre>';
        //    exit;
    }

    // Load and display results
    LoadResults();

    // Add event handler for "Add Task" and "Refresh" Buttons
    document.getElementById('add_task').addEventListener('click', cbAddTask);
    document.getElementById('refresh').addEventListener('click', cbRefresh);

    // Add timed refresh
    ws.refreshTimer = window.setInterval(cbRefresh, 300000);


};

