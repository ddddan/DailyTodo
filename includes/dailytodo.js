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
 * LoadResults() - Load the results via ajax
 * 
 */
function LoadResults() {
    var ws = window.sitescriptdata;

    // AJAX request to load the data 
    var xmlhttp = new XMLHttpRequest;
    xmlhttp.onreadystatechange = function () {
        if (this.readyState === 4) {
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

            // Display table
            DisplayMasterTable('DueDate');

        }
    };

    var AJAXrequest = 'get.php';

    xmlhttp.open('GET', AJAXrequest);
    xmlhttp.send();

}

/**
 * saveDetails() - Save results (user-defined) via ajax
 *  
 * @returns {undefined}
 */

function saveDetails() {
    var ws = window.sitescriptdata;

    // AJAX request to load the data 
    var xmlhttp = new XMLHttpRequest;

    xmlhttp.open('POST', 'put.php', false);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send('newdetails=' + JSON.stringify(ws.newDetails));

    // Failure
    if (xmlhttp.status !== 200) {
        debugLog(this.statusText);
        alert('ERROR: Unable to update the details file.  Please contact technical support');
        return;
    }
    // Success

    // Save details to local storage
    var row = document.getElementById('detail').getAttribute('data-rowid');
    for (var field in ws.newDetails.data) {
        ws.taskData[row][field] = ws.newDetails.data[field];
    }

    // Refresh
    DisplayMasterTable();

    ws.detailChanged = false;
    ws.newDetails = {};

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

    // document.getElementById('content').innerHTML = '<pre>' + JSON.stringify(sortOrder) + '</pre>'; return

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
    var eContent = document.getElementById('content');

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
            eTH.setAttribute('class', 'colHeader')
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
            if (!!val) {
                val = val.replace(/ 00:00:00\.000/, '');
            }

            // Use task abbreviations
            data[i][ws.taskfCols[j]] = val;


            // Formatting replacements (but do not update data)

            if (!!val) {
                val = val.replace(/\n/g, '<br>').replace(/(\<br\>|^)\*/g, '$1&bull;');
                val = val.replace(/\(\(/, '<span class="outside">(').replace(/\)\)/, ')</span>');
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
    var data = window.sitescriptdata.taskData[row];

    // Reset changed status
    window.sitescriptdata.detailChanged = false;

    // Display shaded background (lightbox effect)
    document.getElementById('shade').removeClassName('hidden');

    // Populate detail box
    var eDetail = document.getElementById('detail');
    // User defined attributes to help with tracking the request
    eDetail.setAttribute('data-TaskID', data.TaskID);
    eDetail.setAttribute('data-rowid', row);

    var eHead = document.getElementById('detail_header');
    eHead.textContent = data.CampaignName + ' - ' + data.TaskName;

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
    ePriority.addEventListener('click', cbInputPriority);
    ePriority.addEventListener('change', cbDetailChanged);

    // Add Notes
    var eNotes = document.getElementById('notes');
    eNotes.value = data.Notes || '';
    // ePriorityValue.addEventListener('click', cbInputNotes);
    eNotes.addEventListener('change', cbDetailChanged);

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
        saveDetails();
    }

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
        e.value = 1;
    }
}

/**
 * cbDetailChanged - set changed flag to true to allow saving / cancelling
 * @param {type} evt - The event changed -- to allow updating the new details
 * @returns {undefined}
 */
function cbDetailChanged(evt) {
    var e = evt.currentTarget;
    var ws = window.sitescriptdata;
    ws.detailChanged = true;

    // Update task id
    ws.newDetails.TaskID = document.getElementById('detail').getAttribute('data-TaskID');

    e.value;
    ws.newDetails.data = {};
    ws.newDetails.data[e.getAttribute('name')] = e.value;

}



window.onload = function () {
    LoadResults();
    // DisplayMasterTable('DueDate');
};

