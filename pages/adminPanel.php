<?php

/*
File: 		admin_panel.php
Coder:		Levi Meahan
Created:	11/14/2013
Revised:	12/03/2013 by Levi Meahan
Purpose:	Function for admin panel where user and content data can be submitted and edited
Algorithm:	See master_plan.html
*/

require_once 'admin/formTools.php';

/**
 * @noinspection SqlResolve
 * @noinspection SqlInsertValues
 */
function adminPanel() {
    global $system;
    global $player;
    global $self_link;
    global $id;
    global $RANK_NAMES;

    // Staff level check
    if(!$player->hasAdminPanel()) {
        return false;
    }

    $content_create_pages = [
        'create_ai',
        'create_jutsu',
        'create_item',
        'create_bloodline',
        'create_mission',
        'create_clan',
    ];
    $content_edit_pages = [
        'edit_ai',
        'edit_jutsu',
        'edit_item',
        'edit_bloodline',
        'edit_mission',
        'edit_clan',
    ];

    $user_admin_pages = [
        'create_rank',
        'edit_rank',
        'edit_team',
        'edit_user',
        'activate_user',
        'delete_user',
        'give_bloodline',
    ];


    // Menu
    echo "<table class='table'>
        <tr><th>Admin Panel Menu</th></tr>";
	if($player->isContentAdmin()) {
	    echo "<tr><td style='text-align:center'>";
	    echo implode(
	        "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($self_link) {
                return "<a href='{$self_link}&page={$page_slug}'>" . System::unSlug($page_slug) . "</a>";
            }, $content_create_pages)
        );
	    echo "</td></tr>";
    }
    if($player->isContentAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($self_link) {
                return "<a href='{$self_link}&page={$page_slug}'>" . System::unSlug($page_slug) . "</a>";
            }, $content_edit_pages)
        );
        echo "</td></tr>";
    }
    if($player->isUserAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($self_link) {
                return "<a href='{$self_link}&page={$page_slug}'>" . System::unSlug($page_slug) . "</a>";
            }, $user_admin_pages)
        );
        echo "</td></tr>";
    }
    echo "</table>";

    // Variable sets
    $constraints = require 'admin/entity_constraints.php';

    $page = $_GET['page'] ?? '';

    if(array_search($page, $user_admin_pages) !== false && !$player->isUserAdmin()) {
        $page = '';
    }
    else if(array_search($page, $content_create_pages) !== false && !$player->isContentAdmin()) {
        $page = '';
    }
    else if(array_search($page, $content_edit_pages) !== false && !$player->isContentAdmin()) {
        $page = '';
    }

    // Create NPC
    if($page == 'create_ai') {
        /* Variables
        -ai_id
        -rank
        -name
        -max_health
        -ninjutsu_skill
        -genjutsu_skill
        -taijutsu_skill
        -cast_speed
        -speed
        -strength
        -endurance
        -intelligence
        -willpower
        -moves(json encoded text): [battle_text, power, jutsu_type] */
        /* Variables */
        $variables =& $constraints['ai'];
        $error = false;
        $data = [];
        if($_POST['ai_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `ai_opponents` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message("NPC created!");
                }
                else {
                    throw new Exception("Error creating NPC!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create NPC</th></tr>
		<tr><td>
		<form action='$self_link&page=create_ai' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='ai_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create jutsu
    else if($page == 'create_jutsu') {
        /* Variables
        -jutsu_id
        -name
        -jutsu_type (ninjutsu, genjutsu, taijutsu)
        -rank (student, genin, etc.)
        -power
        -element (poison, fire, etc.)
        -purchase_type (1 = default, 2 = purchasable, 3 = non-purchasable)
        -purchase_cost
        -use_cost
        -offense (ninjutsu, genjutsu, taijutsu)
        -general (strength, intel, will, etc.)
        -second_general (str, int, will, etc)
        -description
        -battle_text
        -effect
        -effect_amount
        -effect_length */
        /* Variables */
        $variables =& $constraints['jutsu'];
        $error = false;
        $data = [];
        if($_POST['jutsu_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                // Hand seals hack
                $query = "INSERT INTO `jutsu` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message("Jutsu created!");
                }
                else {
                    throw new Exception("Error creating jutsu!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            foreach($variables as $var_name => $variable) {
                if(isset($_POST[$var_name])) {
                    $data[$var_name] = htmlspecialchars($_POST[$var_name], ENT_QUOTES);
                }
                else {
                    $data[$var_name] = '';
                }
            }
        }
        else {
            foreach($variables as $var_name => $variable) {
                $data[$var_name] = '';
            }
        }
        echo "<table class='table'><tr><th>Create Jutsu</th></tr>
		<tr><td>
		<form action='$self_link&page=create_jutsu' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='jutsu_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create item
    else if($page == 'create_item') {
        /* Variables
            -item_id
            -name
            -rank
            -purchase_type(1 = purchasable, 2 = event)
            -purchase_cost
            -use_type (1 = weapon, 2 = armor, 3 = consumable)
            -effect
            -effect_amount */
        $table_name = 'items';
        /* Variables */
        $variables =& $constraints['item'];
        $error = false;
        $data = [];
        if($_POST['item_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message("Item created!");
                }
                else {
                    throw new Exception("Error creating item!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            foreach($variables as $var_name => $variable) {
                if(isset($_POST[$var_name])) {
                    $data[$var_name] = htmlspecialchars($_POST[$var_name], ENT_QUOTES);
                }
                else {
                    $data[$var_name] = '';
                }
            }
        }
        else {
            foreach($variables as $var_name => $variable) {
                $data[$var_name] = '';
            }
        }
        echo "<table class='table'><tr><th>Create Item</th></tr>
		<tr><td>
		<form action='$self_link&page=create_item' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='item_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create Bloodline
    else if($page == 'create_bloodline') {
        $table_name = 'bloodlines';
        $content_name = 'bloodline';
        /* Variables */
        $variables =& $constraints['bloodline'];
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create rank
    else if($page == 'create_rank') {
        $table_name = 'ranks';
        $content_name = 'rank';
        /* Variables */
        $variables =& $constraints['rank'];
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create Clan
    else if($page == 'create_clan') {
        $table_name = 'clans';
        $content_name = 'clan';
        /* Variables */
        $variables =& $constraints['create_clan'];
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create Clan
    else if($page == 'create_mission') {
        $table_name = 'missions';
        $content_name = 'mission';
        /* Variables */
        $variables =& $constraints['mission'];
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Edit NPC
    else if($page == 'edit_ai') {
        /* Variables */
        $variables =& $constraints['ai'];
        $select_ai = true;
        // Validate NPC id
        if($_POST['ai_id']) {
            $ai_id = (int)$system->clean($_POST['ai_id']);
            $result = $system->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$ai_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid NPC!");
                $system->printMessage();
            }
            else {
                $ai_data = $system->db_fetch($result);
                $select_ai = false;
            }
        }
        // POST submit edited data
        if($_POST['ai_data'] && !$select_ai) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `ai_opponents` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `ai_id`='$ai_id'";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message("NPC " . $data['name'] . " has been edited!");
                    $select_ai = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_ai = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($ai_data && !$select_ai) {
            $data =& $ai_data;
            echo "<table class='table'><tr><th>Edit NPC (" . stripslashes($ai_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_ai' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='hidden' name='ai_id' value='{$ai_data['ai_id']}' />
			<input type='submit' name='ai_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_ai) {
            $result = $system->query("SELECT `ai_id`, `name` FROM `ai_opponents`");
            echo "<table class='table'><tr><th>Select NPC</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_ai' method='post'>
			<select name='ai_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='{$row['ai_id']}'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit jutsu
    else if($page == 'edit_jutsu') {
        $select_jutsu = true;
        /* Variables */
        $variables =& $constraints['jutsu'];
        // Validate jutsu id
        if($_POST['jutsu_id']) {
            $jutsu_id = (int)$system->clean($_POST['jutsu_id']);
            $result = $system->query("SELECT * FROM `jutsu` WHERE `jutsu_id`='$jutsu_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid Jutsu!");
                $system->printMessage();
            }
            else {
                $jutsu_data = $system->db_fetch($result);
                $select_jutsu = false;
            }
        }
        // POST submit edited data
        if($_POST['jutsu_data'] && !$select_jutsu) {
            try {
                $content_id = $jutsu_id;
                $data = [];
                validateFormData($variables, $data, $content_id);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `jutsu` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `jutsu_id`='{$jutsu_data['jutsu_id']}'";
                //echo $query;
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message("Jutsu edited!");
                    $select_jutsu = true;
                }
                else {
                    throw new Exception("Error editing jutsu!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // Form for editing data
        if($jutsu_data && !$select_jutsu) {
            $data =& $jutsu_data;
            echo "<table class='table'><tr><th>Edit Jutsu (" . stripslashes($jutsu_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_jutsu' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Jutsu ID:</label> $jutsu_id<br />";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='hidden' name='jutsu_id' value='{$jutsu_data['jutsu_id']}' />
			<input type='submit' name='jutsu_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_jutsu) {
            $jutsu_array = [];
            $result = $system->query("SELECT `jutsu_id`, `name`, `jutsu_type`, `power`, `effect`, `effect_amount`, `effect_length`,
				`purchase_cost`, `element`, `rank` 
				FROM `jutsu` ORDER BY `rank` ASC, `purchase_cost` ASC"
            );
            while($row = $system->db_fetch($result)) {
                $jutsu_array[$row['jutsu_id']] = $row;
            }
            echo "<table class='table'><tr><th colspan='3'>Select Jutsu</th></tr>
			<tr>
				<th>Ninjutsu</th>
				<th>Taijutsu</th>
				<th>Genjutsu</th>
			</tr>
			<tr>
			<!--NINJUTSU-->
			<td>		
				<form action='$self_link&page=edit_jutsu' method='post'>
				<select name='jutsu_id'>";
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != 'ninjutsu') {
                    continue;
                }
                echo "<option value='$id'>" . stripslashes($jutsu['name']) . "</option>";
            }
            echo "</select>
				<input type='submit' value='Select' />
				</form>
			</td>
			<!--TAIJUTSU-->
			<td>
			<form action='$self_link&page=edit_jutsu' method='post'>
			<select name='jutsu_id'>";
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != 'taijutsu') {
                    continue;
                }
                echo "<option value='$id'>" . stripslashes($jutsu['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td>
			<!--GENJUTSU-->
			<td>
			<form action='$self_link&page=edit_jutsu' method='post'>
			<select name='jutsu_id'>";
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != 'genjutsu') {
                    continue;
                }
                echo "<option value='$id'>" . stripslashes($jutsu['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
            $jutsu_type = 'ninjutsu';
            if($_GET['jutsu_type']) {
                switch($_GET['jutsu_type']) {
                    case 'ninjutsu':
                        $jutsu_type = 'ninjutsu';
                        break;
                    case 'taijutsu':
                        $jutsu_type = 'taijutsu';
                        break;
                    case 'genjutsu':
                        $jutsu_type = 'genjutsu';
                        break;
                }
            }
            $style = "style='text-decoration:none;'";
            // Filter links
            echo "<p style='text-align:center;margin-bottom:0;'>
				<a href='$self_link&page=edit_jutsu&jutsu_type=ninjutsu' " .
                ($jutsu_type == 'ninjutsu' ? $style : "") . ">Ninjutsu</a> |
				<a href='$self_link&page=edit_jutsu&jutsu_type=taijutsu' " .
                ($jutsu_type == 'taijutsu' ? $style : "") . ">Taijutsu</a> |
				<a href='$self_link&page=edit_jutsu&jutsu_type=genjutsu' " .
                ($jutsu_type == 'genjutsu' ? $style : "") . ">Genjutsu</a>
			</p>";
            // Show lists
            echo "<table class='table' style='margin-top:15px;'><tr>
				<th style='width:25%;'>Name</th>
				<th style='width:8%;'>Power</th>
				<th style='width:30%;'>Effect</th>
				<th style='width:18%;'>Element</th>
				<th style='width:19%;'>Cost</th>
			</tr>";
            echo "<tr><th colspan='5'>" . $RANK_NAMES[1] . "</th></tr>";
            $current_rank = 1;
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != $jutsu_type) {
                    continue;
                }
                if($jutsu['rank'] > $current_rank) {
                    $current_rank = $jutsu['rank'];
                    echo "<tr><th colspan='5'>" . $RANK_NAMES[$current_rank] . "</th></tr>";
                }
                echo "<tr>
					<td>" . $jutsu['name'] . "</td>
					<td>" . $jutsu['power'] . "</td>
					<td>" . ucwords(str_replace('_', ' ', $jutsu['effect'])) . ($jutsu['effect'] == 'none' ? '' :
                        " (" . $jutsu['effect_amount'] . "% / " . $jutsu['effect_length'] . ")") . "</td>
					<td>" . ucwords($jutsu['element']) . "</td>
					<td>&yen;" . $jutsu['purchase_cost'] . "</td>
				</tr>";
            }
            echo "</table>";
        }
    }
    // Edit item
    else if($page == 'edit_item') {
        $select_item = true;
        $table_name = 'items';
        /* Variables */
        $variables =& $constraints['item'];
        // Validate item id
        if($_POST['item_id']) {
            $item_id = (int)$system->clean($_POST['item_id']);
            $result = $system->query("SELECT * FROM `$table_name` WHERE `item_id`='$item_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid item!");
                $system->printMessage();
            }
            else {
                $item_data = $system->db_fetch($result);
                $select_item = false;
            }
        }
        // POST submit edited data
        if($_POST['item_data'] && !$select_item) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `item_id`='{$item_data['item_id']}'";
                //echo $query;
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message("Item edited!");
                    $select_item = true;
                }
                else {
                    throw new Exception("Error editing item!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // Form for editing data
        if($item_data && !$select_item) {
            $data =& $item_data;
            echo "<table class='table'><tr><th>Edit Item (" . stripslashes($item_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_item' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            foreach($variables as $var_name => $variable) {
                if($variable['input_type'] == 'text') {
                    echo "<label for='$var_name'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
					<input type='text' name='$var_name' value='" . stripslashes($data[$var_name]) . "' /><br />";
                }
                else if($variable['input_type'] == 'text_area') {
                    echo "<label for='$var_name'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label><br />
		                <label></label>&nbsp;<textarea name='$var_name'>" . stripslashes($data[$var_name]) . "</textarea><br />";
                }
                else if($variable['input_type'] == 'radio' && !empty($variable['options'])) {
                    echo "<label for='$var_name' style='margin-top:5px;'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
					<p style='padding-left:10px;margin-top:5px;'>";
                    $count = 1;
                    foreach($variable['options'] as $id => $option) {
                        if($variable['data_type'] == 'int' || $variable['data_type'] == 'float') {
                            echo "<input type='radio' name='$var_name' value='$count' " .
                                ($data[$var_name] == $count ? "checked='checked'" : '') .
                                " />" . ucwords(str_replace("_", " ", $option));
                            $count++;
                        }
                        else if($variable['data_type'] == 'string') {
                            echo "<input type='radio' name='$var_name' value='$option' " .
                                ($data[$var_name] == $option ? "checked='checked'" : '') .
                                " />" . ucwords(str_replace("_", " ", $option));
                        }
                        echo "<br />";
                    }
                    echo "</p>";
                }
                else {
                    echo "Coming soon!<br />";
                }
            }
            echo "<br />
			<input type='hidden' name='item_id' value='{$item_data['item_id']}' />
			<input type='submit' name='item_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_item) {
            $result = $system->query("SELECT `item_id`, `name`, `effect`, `effect_amount`, `use_type`, `purchase_cost` 
				FROM `$table_name`"
            );
            $item_array = [];
            while($row = $system->db_fetch($result)) {
                $item_array[$row['item_id']] = $row;
            }
            echo "<table class='table'><tr><th>Select Item</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_item' method='post'>
			<select name='item_id'>";
            foreach($item_array as $id => $item) {
                echo "<option value='$id'>" . stripslashes($item->name) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
            $item_type = 1;
            if(isset($_GET['item_type'])) {
                switch($_GET['item_type']) {
                    case 'weapon':
                        $item_type = 1;
                        break;
                    case 'armor':
                        $item_type = 2;
                        break;
                    case 'consumable':
                        $item_type = 3;
                        break;
                }
            }
            $style = "style='text-decoration:none;'";
            // Filter links
            echo "<p style='text-align:center;margin-bottom:0;'>
				<a href='$self_link&page=edit_item&item_type=weapon' " .
                ($item_type == 1 ? $style : "") . ">Weapons</a> |
				<a href='$self_link&page=edit_item&item_type=armor' " .
                ($item_type == 2 ? $style : "") . ">Armor</a> |
				<a href='$self_link&page=edit_item&item_type=consumable' " .
                ($item_type == 3 ? $style : "") . ">Consumables</a>
			</p>";
            // Show lists
            echo "<table class='table' style='margin-top:15px;'><tr>
				<th style='width:25%;'>Name</th>
				<th style='width:10%;'>Power</th>
				<th style='width:25%;'>Effect</th>
				<th style='width:20%;'>Cost</th>
			</tr>";
            foreach($item_array as $id => $item) {
                if($item->use_type != $item_type) {
                    continue;
                }
                echo "<tr>
					<td>" . $item->name . "</td>
					<td>" . $item->effect_amount . "</td>
					<td>" . ucwords(str_replace('_', ' ', $item->effect)) . "</td>
					<td>&yen;" . $item->purchase_cost . "</td>
				</tr>";
            }
            echo "</table>";
        }
    }
    // Edit Bloodline
    else if($page == 'edit_bloodline') {
        $table_name = 'bloodlines';
        $content_name = 'bloodline';
        /* Variables */
        $variables =& $constraints['bloodline'];
        $select_content = true;
        // Validate NPC id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit NPC
    else if($page == 'edit_rank') {
        $table_name = 'ranks';
        $content_name = 'rank';
        /* Variables */
        $variables =& $constraints['rank'];
        $select_content = true;
        // Validate content id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit Clan
    else if($page == 'edit_clan') {
        $table_name = 'clans';
        $content_name = 'clan';
        /* Variables */
        $variables =& $constraints['edit_clan'];

        $select_content = true;
        // Validate NPC id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Clan ID:</label> " . $content_data['clan_id'] . "<br />";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit Team
    else if($page == 'edit_team') {
        $table_name = 'teams';
        $content_name = 'team';
        /* Variables */
        $variables =& $constraints['team'];

        $select_content = true;
        // Validate NPC id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Team ID:</label> " . $content_data['team_id'] . "<br />";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit Mission
    else if($page == 'edit_mission') {
        $table_name = 'missions';
        $content_name = 'mission';
        /* Variables */
        $variables =& $constraints['mission'];

        $select_content = true;
        // Validate content id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Mission ID:</label> " . $content_data['mission_id'] . "<br />";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    /* USER ADMINISTRATION PAGES */
    else if($page == 'edit_user') {
        $select_user = true;
        /* Variables */
        $variables =& $constraints['edit_user'];

        if($player->isHeadAdmin()) {
            $variables['staff_level'] = [
                'data_type' => 'int',
                'input_type' => 'radio',
                'options' => [
                    User::STAFF_NONE => 'normal_user',
                    User::STAFF_MODERATOR => 'moderator',
                    User::STAFF_HEAD_MODERATOR => 'head moderator',
                    User::STAFF_CONTENT_ADMIN => 'content admin',
                    User::STAFF_ADMINISTRATOR => 'administrator',
                    User::STAFF_HEAD_ADMINISTRATOR => 'head administrator'
                ],
            ];
        }

        if($player->isSupportAdmin() || $player->isUserAdmin()) {
            $variables['support_level'] = [
                'data_type' => 'int',
                'input_type' => 'radio',
                'options' => [
                    User::SUPPORT_NONE => 'normal_user',
                    User::SUPPORT_BASIC => 'basic_support',
                    User::SUPPORT_INTERMEDIATE => 'intermediate_support',
                    User::SUPPORT_CONTENT_ONLY => 'content_only_support',
                    User::SUPPORT_SUPERVISOR => 'support_supervisor',
                    User::SUPPORT_ADMIN => 'support_admin',
                ]
            ];
        }

        // Validate user name
        if($_GET['user_name']) {
            $user_name = $system->clean($_GET['user_name']);
            $result = $system->query("SELECT * FROM `users` WHERE `user_name`='$user_name'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid user!");
                $system->printMessage();
            }
            else {
                $user_data = $system->db_fetch($result);
                $select_user = false;
            }
        }
        // POST submit edited data
        if($_POST['user_data'] && !$select_user) {
            try {
                // Load form data
                $data = [];
                validateFormData($variables, $data);

                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `users` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `user_id`='{$user_data['user_id']}'";
                // echo $query;
                $system->query($query);

                if($system->db_last_affected_rows == 1) {
                    $system->message("User edited!");
                    $select_user = true;
                    if($user_data['user_id'] == $player->user_id) {
                        $player->loadData();
                    }
                }
                else {
                    throw new Exception("Error editing user!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // Form for editing data
        if($user_data && !$select_user) {
            $data =& $user_data;
            echo "<table class='table'><tr><th>Edit User (" . stripslashes($data['user_name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_user&user_name={$data['user_name']}' method='post'>
			<style>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='hidden' name='user_name' value='{$data['user_name']}' />
			<input type='submit' name='user_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_user) {
            echo "<table class='table'><tr><th>Edit User</th></tr>
			<tr><td style='text-align:center;'>
			<form action='$self_link&page=edit_user' method='get'>
			<b>Username</b><br />
			<input type='hidden' name='page' value='edit_user' />
			<input type='hidden' name='id' value='{$_GET['id']}'' />
			<input type='text' name='user_name' /><br />
			<input type='submit' value='Edit' />
			</form>
			</td></tr></table>";
        }
    }
    // Activate user
    else if($page == 'activate_user') {
        if($_POST['activate']) {
            $activate = $system->clean($_POST['activate']);
            $system->query("UPDATE `users` SET `user_verified`='1' WHERE `user_name`='$activate' LIMIT 1");
            if($system->db_last_affected_rows == 1) {
                $system->message("User activated!");
            }
            else {
                $system->message("Error activating user! (Invalid username, or user has already been activated)");
            }
            $system->printMessage();
        }
        echo "<table class='table'><tr><th>Activate User</th></tr>
		<tr><td style='text-align:center;'>
			<form action='$self_link&page=activate_user' method='post'>
			<b>-Username-</b><br />
			<input type='text' name='activate' /><br />
			<input type='submit' value='Activate' />
			</form>
		</td></tr></table>";
    }
    // Delete user
    else if($page == 'delete_user') {
        $select_user = true;
        if($_POST['user_name']) {
            $user_name = $system->clean($_POST['user_name']);
            try {
                $result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
                if($system->db_last_num_rows == 0) {
                    throw new Exception("Invalid user!");
                }
                $result = $system->db_fetch($result);
                $user_id = $result['user_id'];
                $user_name = $result['user_name'];
                if($result['staff_level'] >= $player->staff_level && !$player->isHeadAdmin()) {
                    throw new Exception("You cannot delete other admins!");
                }
                if(!isset($_POST['confirm'])) {
                    echo "<table class='table'><tr><th>Delete User</th></tr>
					<tr><td style='text-align:center;'>
						<form action='$self_link&page=delete_user' method='post'>
						Are you sure you want to delete <b>$user_name</b>?<br />
						<input type='hidden' name='user_name' value='$user_name' />
						<input type='hidden' name='confirm' value='1' />
						<input type='submit' name='Confirm Deletion' />
						</form>
					</td></tr></table>";
                    $select_user = false;
                    throw new Exception('');
                }
                // Success, delete
                $system->query("DELETE FROM `users` WHERE `user_id`='$user_id' LIMIT 1");
                $system->query("DELETE FROM `user_inventory` WHERE `user_id`='$user_id' LIMIT 1");
                $system->query("DELETE FROM `user_bloodlines` WHERE `user_id`='$user_id' LIMIT 1");
                $system->message("User <b>$user_name</b> deleted.");
                // */
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
        }
        if($select_user) {
            $system->printMessage();
            echo "<table class='table'><tr><th>Delete User</th></tr>
			<tr><td style='text-align:center;'>
				<form action='$self_link&page=delete_user' method='post'>
				<b>Username</b><br />
				<input type='text' name='user_name' /><br />
				<input type='submit' name='Delete' />
				</form>
			</td></tr></table>";
        }
    }
    // Give bloodline
    else if($page == 'give_bloodline') {
        // Fetch BL list
        $result = $system->query("SELECT `bloodline_id`, `name` FROM `bloodlines`");
        if($system->db_last_num_rows == 0) {
            $system->message("No bloodlines in database!");
            $system->printMessage();
            return false;
        }
        $bloodlines = [];
        while($row = $system->db_fetch($result)) {
            $bloodlines[$row['bloodline_id']]['name'] = $row['name'];
        }
        if($_POST['give_bloodline']) {
            $bloodline_id = (int)$system->clean($_POST['bloodline_id']);
            $user_name = $system->clean($_POST['user_name']);
            try {
                if(!isset($bloodlines[$bloodline_id])) {
                    throw new Exception("Invalid bloodline!");
                }
                $result = $system->query("SELECT `user_id` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
                if($system->db_last_num_rows == 0) {
                    throw new Exception("User does not exist!");
                }
                $result = $system->db_fetch($result);
                $user_id = $result['user_id'];
                $status = giveBloodline($bloodline_id, $user_id);
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        echo "<table class='table'><tr><th>Give Bloodline</th></tr>
		<tr><td>
		<form action='$self_link&page=give_bloodline' method='post'>
		<b>Bloodline</b><br />
		<select name='bloodline_id'>";
        foreach($bloodlines as $id => $bloodline) {
            echo "<option value='" . $id . "'>" . stripslashes($bloodline['name']) . "</option>";
        }
        echo "</select><br />
		<b>Username</b><br />
		<input type='text' name='user_name' /><br />
		<input type='submit' name='give_bloodline' value='Select' />
		</form>
		</td></tr></table>";
    }
}

function giveBloodline($bloodline_id, $user_id, $display = true) {
    global $system;
    $result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id` = '$bloodline_id' LIMIT 1");
    if($system->db_last_num_rows == 0) {
        throw new Exception("Invalid bloodline!");
    }
    $bloodline = $system->db_fetch($result);

    $user_bloodline['bloodline_id'] = $bloodline['bloodline_id'];
    $user_bloodline['name'] = $bloodline['name'];
    $user_bloodline['passive_boosts'] = $bloodline['passive_boosts'];
    $user_bloodline['combat_boosts'] = $bloodline['combat_boosts'];
    $user_bloodline['jutsu'] = $bloodline['jutsu'];
    // 5000 bl skill -> 20 power = 1 increment of BL effect
    // Heal: 1 increment = 100 heal

    $effects = [
        // Passive boosts
        'scout_range' => [
            'multiplier' => 0.00004,
        ],
        'stealth' => [
            'multiplier' => 0.00004,
        ],
        'regen' => [
            'multiplier' => 0.0001,
        ],
        // Combat boosts
        'heal' => [
            'multiplier' => 0.001,
        ],
        'ninjutsu_boost' => [
            'multiplier' => 0.01,
        ],
        'taijutsu_boost' => [
            'multiplier' => 0.01,
        ],
        'genjutsu_boost' => [
            'multiplier' => 0.01,
        ],
        'ninjutsu_resist' => [
            'multiplier' => 0.01,
        ],
        'taijutsu_resist' => [
            'multiplier' => 0.01,
        ],
        'genjutsu_resist' => [
            'multiplier' => 0.01,
        ],
        'speed_boost' => [
            'multiplier' => 0.001,
        ],
        'cast_speed_boost' => [
            'multiplier' => 0.001,
        ],
        'endurance_boost' => [
            'multiplier' => 0.001,
        ],
        'intelligence_boost' => [
            'multiplier' => 0.001,
        ],
        'willpower_boost' => [
            'multiplier' => 0.001,
        ],
    ];
    if($user_bloodline['passive_boosts']) {
        $user_bloodline['passive_boosts'] = json_decode($user_bloodline['passive_boosts'], true);
        foreach($user_bloodline['passive_boosts'] as $id => $boost) {
            if(!isset($effects[$boost['effect']])) {
            }
            else {
                $user_bloodline['passive_boosts'][$id]['power'] = round($boost['power'] * $effects[$boost['effect']]['multiplier'], 6);
            }
        }
        $user_bloodline['passive_boosts'] = json_encode($user_bloodline['passive_boosts']);
    }
    if($user_bloodline['combat_boosts']) {
        $user_bloodline['combat_boosts'] = json_decode($user_bloodline['combat_boosts'], true);
        foreach($user_bloodline['combat_boosts'] as $id => $boost) {
            if(!isset($effects[$boost['effect']])) {
            }
            else {
                $user_bloodline['combat_boosts'][$id]['power'] = round($boost['power'] * $effects[$boost['effect']]['multiplier'], 6);
            }
        }
        $user_bloodline['combat_boosts'] = json_encode($user_bloodline['combat_boosts']);
    }

    // move ids (level & exp -> 0)
    $user_bloodline['jutsu'] = false;
    $result = $system->query("SELECT `bloodline_id` FROM `user_bloodlines` WHERE `user_id`='$user_id' LIMIT 1");

    // Insert new row
    if($system->db_last_num_rows == 0) {
        $query = "INSERT INTO `user_bloodlines` (`user_id`, `bloodline_id`, `name`, `passive_boosts`, `combat_boosts`, `jutsu`)
			VALUES ('$user_id', '$bloodline_id', '{$user_bloodline['name']}', '{$user_bloodline['passive_boosts']}', 
			'{$user_bloodline['combat_boosts']}', '{$user_bloodline['jutsu']}')";
    }

    // Update existing row
    else {
        $query = "UPDATE `user_bloodlines` SET
			`bloodline_id` = '$bloodline_id',
			`name` = '{$user_bloodline['name']}',
			`passive_boosts` = '{$user_bloodline['passive_boosts']}',
			`combat_boosts` = '{$user_bloodline['combat_boosts']}',
			`jutsu` = '{$user_bloodline['jutsu']}'
			WHERE `user_id`='$user_id' LIMIT 1";
    }
    $system->query($query);

    if($system->db_last_affected_rows == 1) {
        if($display) {
            $system->message("Bloodline given!");
        }
        $result = $system->query("SELECT `exp`, `bloodline_skill` FROM `users` WHERE `user_id`='$user_id' LIMIT 1");
        $result = $system->db_fetch($result);
        $new_exp = $result['exp'];
        $new_bloodline_skill = $result['bloodline_skill'];
        if($result['bloodline_skill'] > 10) {
            $bloodline_skill_reduction = ($result['bloodline_skill'] - 10) * Bloodline::SKILL_REDUCTION_ON_CHANGE;
            $new_exp -= $bloodline_skill_reduction * 10;
            $new_bloodline_skill -= $bloodline_skill_reduction;
        }

        $query = "UPDATE `users` SET 
            `bloodline_id`='$bloodline_id', 
            `bloodline_name`='{$bloodline['name']}', 
            `bloodline_skill`='{$new_bloodline_skill}',
            `exp`='{$new_exp}'
			WHERE `user_id`='$user_id' LIMIT 1";

        $system->query($query);
        if($user_id == $_SESSION['user_id']) {
            global $player;
            $player->bloodline_id = $bloodline_id;
            $player->bloodline_name = $bloodline['name'];
            $player->exp = $new_exp;
            $player->bloodline_skill = $new_bloodline_skill;
        }
    }
    else {
        throw new Exception("Error giving bloodline! (Or user already has this BL)");
    }

    if($display) {
        $system->printMessage();
    }
    return true;
}
