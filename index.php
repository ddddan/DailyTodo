<?php
/**
 * DailyTodo - Report new, modified and current tasks from Critical Path
 * 
 * Author: Daniel G. Mullin
 * 
 * Last Modified: 2014-08-19
 *  
 */
// phpinfo(); exit;

require_once('includes/defs.php');
require_once('includes/functions.php');


date_default_timezone_set('America/Toronto');
error_reporting(E_ALL);

// Debug mode
$debug = false;
if (filter_input(INPUT_GET, 'debug') === 'dm20141027') {
    $debug = true;
}
?>

<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>My Todo List</title>
        <link rel="stylesheet" href="includes/dailytodo.css" type="text/css" />
    </head>
    <body>
        <div id="content">
            <div id="shade" class="hidden"></div>
            <div id="loading" class="ajax hidden">
                <h3>Loading...</h3>
                <img src="img/ajax-loader.gif" alt="(via AJAX)" />
            </div>
            <div id="submitting" class="ajax hidden">
                <h3>Submitting...</h3>
                <img src="img/ajax-loader.gif" alt="(via AJAX)" />
            </div>
            <div id="counts" class="no-print">
                <table id="tcounts">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>My Tasks</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="counts_label status_late" id="pastDue_label">Past Due</td>
                            <td class="counts_value status_late" id="pastDue_mine"></td>
                            <td class="counts_value status_late" id="pastDue_total"></td>
                        </tr>
                        <tr>
                            <td class="counts_label status_soon" id="dueSoon_label">Due Soon</td>
                            <td class="counts_value status_soon" id="dueSoon_mine"></td>
                            <td class="counts_value status_soon" id="dueSoon_total"></td>
                        </tr>
                        <tr>
                            <td class="counts_label" id="onRadar_label">On Radar</td>
                            <td class="counts_value" id="onRadar_mine"></td>
                            <td class="counts_value" id="onRadar_total"></td>
                        </tr>
                        <tr id="totalCountRow">
                            <td class="counts_label" id="totalCount_label">Total</td>
                            <td class="counts_value" id="totalCount_mine"></td>
                            <td class="counts_value" id="totalCount_total"></td>
                        </tr>
                        <tr id="buttonsRow">
                            <td colspan="100%">
                                <a id="add_task" href="#" class="button grey">Add Task</a>
                                <a id="refresh" href="#" class="button grey">Refresh</a>
                            </td>
                        </tr>
                        <tr id="timeRow">
                            <td colspan="100%">
                                <p class="timestamp">Last refresh: <?php echo date('Y-m-d H:i:s'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div id="detail" class="popup hidden">
                <h3 id="detail_header" class="popup_header"></h3>
                <div id="detail_close" class="popup_close">X</div>
                <div id="detail_body" class="popup_body">
                    <div id="detail_date" class="field">
                        <span class="label">Due Date: </span>   
                        <span id="due_date_value"></span>
                    </div>
                    <div id="detail_status" class="field">
                        <span class="label">Status: </span>
                        <span id="status_value"></span>
                    </div>
                    <div id="detail_priority" class="field">
                        <span class="label">Priority: </span>
                        <input id="priority" name="Priority" type="number" min="0" max="99">
                        <div id="detail_priority_adjust" class="subfield hidden">
                            <input id="priority_adjust" name="AdjustPriorities" type="checkbox" checked />
                            <label for="priority_adjust">Adjust Priorities?</label>
                        </div>
                    </div>
                    <div id="detail_completed" class="field">
                        <span class="label">Completed: </span>
                        <input id="completed" name="Completed" type="checkbox" />
                    </div>
                    <div id="detail_notes" class="field">
                        <p class="label">Notes</p>
                        <textarea id="notes" name="Notes" cols="70" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div id="addtask" class="popup hidden">
                <h3 id="addtask_header" class="popup_header">Add New Task</h3>
                <div id="addtask_close" class="popup_close">X</div>
                <div id="addtask_body" class="popup_body">
                    <div id="addtask_message">
                        <p>Enter your task details below.</p>
                    </div>
                    <form id="newtask">
                        <table id="addtask_grid">
                            <tbody>
                                <tr>
                                    <td class="addtask_label">
                                        <label for="newtask_type">Task&nbsp;Type:</label>
                                    </td>
                                    <td class="addtask_value">
                                        <select id="newtask_type" name="newtask_type" class="newtask_value">
                                            <option default value="-1">Please select....</option>
                                        </select>
                                    </td>
                                    <td class="addtask_label">
                                        <label for="newtask_name">Task&nbsp;Name:</label>
                                    </td>
                                    <td class="addtask_value">
                                        <input type="text" id="newtask_taskName" name="newtask_taskName" class="newtask_value" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="addtask_label">
                                        <label for="newtask_docket">Docket:</label>
                                    </td>
                                    <td class="addtask_value">
                                        <input type="text" id="newtask_docket" name="newtask_docket" class="newtask_value">
                                    </td>
                                    <td class="addtask_label">
                                        <label for="newtask_client">Client:</label>
                                    </td>
                                    <td class="addtask_value">
                                        <select id="newtask_client" name="newtask_client" class="newtask_value">
                                            <option default value="-1">Please select....</option>
                                        </select>                        
                                    </td>
                                </tr>
                                <tr>
                                    <td class="addtask_label">
                                        <label for="newtask_duedate">Due&nbsp;Date:</label>
                                    </td>
                                    <td class="addtask_value">
                                        <input type="date" id="newtask_dueDate" name="newtask_dueDate" class="newtask_value" required>
                                    </td>
                                    <td class="addtask_label">
                                        <label for="newtask_client">Priority:</label>
                                    </td>
                                    <td class="addtask_value">
                                        <input id="newtask_priority" name="Priority" type="number" min="0" max="99" class="newtask_value">                     
                                    </td>
                                </tr>
                                <tr id="addtask_notes">
                                    <td class="addtask_label">
                                        <label for="newtask_notes">Notes:</label>
                                    </td>
                                    <td class=addtask_value colspan="3">
                                        <textarea id="newtask_notes" name="Notes" cols="70" rows="4" class="newtask_value"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="100%" id="addtask_buttons">
                                        <a id="addtask_submit" href="#" class="button grey">Submit</a>
                                        <a id="addtask_cancel" href="#" class="button grey">Cancel</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>

            </div>
            <div id="maintable"></div>
            <?php if ($debug) { ?>
                <div id="debug"></div>
            <?php } ?>
        </div>
        <?php require_once ('includes/dailytodo.js.php'); // Script needing PHP      ?>
        <script type="text/javascript" src="includes/functions.js"></script>
        <script type="text/javascript" src="includes/dailytodo.js"></script>
    </body>
</html>
