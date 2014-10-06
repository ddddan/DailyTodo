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
            <div id="detail" class="hidden">
                <h3 id="detail_header"></h3>
                <div id="detail_close">X</div>
                <div id="detail_body">
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
                    </div>
                    <div id="detail_notes" class="field">
                        <p class="label">Notes</p>
                        <textarea id="notes" name="Notes" cols="70" rows="4"></textarea>
                </div>
            </div>
        </div>
        <?php require_once ('includes/dailytodo.js.php'); // Script needing PHP     ?>
        <script type="text/javascript" src="includes/functions.js"></script>
        <script type="text/javascript" src="includes/dailytodo.js"></script>
    </body>
</html>
