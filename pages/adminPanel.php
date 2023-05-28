<?php /** @noinspection SqlWithoutWhere */

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
 * @throws Exception
 */
function adminPanel() {
    global $system;
    global $player;
    global $id;
    global $self_link;

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
        'logs',
        'stat_cut',
        'dev_tools'
    ];

    // Menu
    echo "<table class='table'>
        <tr><th>Admin Panel Menu</th></tr>";
    if($player->isContentAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($system) {
                return "<a href='{$system->router->getUrl('admin', ['page' => $page_slug])}'>" . System::unSlug($page_slug) . "</a>";
            }, $content_create_pages)
        );
        echo "</td></tr>";
    }
    if($player->isContentAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($system) {
                return "<a href='{$system->router->getUrl('admin', ['page' => $page_slug])}'>" . System::unSlug($page_slug) . "</a>";
            }, $content_edit_pages)
        );
        echo "</td></tr>";
    }
    if($player->isUserAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($system) {
                return "<a href='{$system->router->getUrl('admin', ['page' => $page_slug])}'>" . System::unSlug($page_slug) . "</a>";
            }, $user_admin_pages)
        );
        echo "</td></tr>";
    }
    echo "</table>";

    // Variable sets
    $constraints = require 'admin/entity_constraints.php';

    $page = $_GET['page'] ?? '';

    if(in_array($page, $user_admin_pages) && !$player->isUserAdmin()) {
        $page = '';
    }
    else if(in_array($page, $content_create_pages) && !$player->isContentAdmin()) {
        $page = '';
    }
    else if(in_array($page, $content_edit_pages) && !$player->isContentAdmin()) {
        $page = '';
    }

    $RANK_NAMES = RankManager::fetchNames($system);

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
		<form action='{$system->router->getUrl('admin', ['page' => 'create_ai'])}' method='post'>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='ai_data' value='Create' />
		</form>
		</td></tr></table>";
    }

    // Create jutsu
    else if($page == 'create_jutsu') {
        require 'admin/jutsu.php';
        createJutsuPage($system, $RANK_NAMES);
    }
    // Edit jutsu
    else if($page == 'edit_jutsu') {
        require 'admin/jutsu.php';
        editJutsuPage($system, $RANK_NAMES);
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
		<form action='$system->router->getUrl('admin', ['page' => 'create_item'])' method='post'>";
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
		<form action='" . $system->router->getUrl('admin', ['page' => "create_{$content_name}"]) . "' method='post'>";
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
		<form action='$system->router->getUrl('admin', ['page' => 'create_' . $content_name])' method='post'>";
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
		<form action='" . $system->router->getUrl('admin', ['page' => "create_{$content_name}"]) . "' method='post'>";
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
		<form action='{$system->router->getUrl('admin', ['page' => 'create_' . $content_name])}' method='post'>";
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
        $self_link = $system->router->getUrl('admin', ['page' => "edit_ai"]);

        // Validate NPC id
        if(!empty($_GET['npc_id'])) {
            $npc_id = (int)$system->clean($_GET['npc_id']);
            $result = $system->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$npc_id'");
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
        if(!empty($_POST['ai_data']) && !$select_ai) {
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
                $query .= "WHERE `ai_id`='$npc_id'";
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
        if(isset($ai_data) && !$select_ai) {
            $data =& $ai_data;
            echo "<table class='table'><tr><th>Edit NPC (" . stripslashes($ai_data['name']) . ")</th></tr>
			<tr><td>
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_ai', 'npc_id' => $ai_data['ai_id']])}' method='post'>";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='submit' name='ai_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_ai) {
            $all_npcs = [];
            $result = $system->query("SELECT * FROM `ai_opponents` ORDER BY `level` ASC");
            while($row = $system->db_fetch($result)) {
                $all_npcs[$row['ai_id']] = $row;
            }

            require 'templates/admin/edit_npc_select.php';
        }
    }
    // Edit item
    else if($page == 'edit_item') {
        $item_being_edited = null;
        $table_name = 'items';
        $self_link = $system->router->getUrl('admin', ['page' => "edit_item"]);

        /* Variables */
        $variables =& $constraints['item'];

        // Validate item id
        if(!empty($_GET['item_id'])) {
            $item_id = (int)$_GET['item_id'];

            $result = $system->query("SELECT * FROM `$table_name` WHERE `item_id`='$item_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid item!");
                $system->printMessage();
            }
            else {
                $item_being_edited = $system->db_fetch($result);
            }
        }

        // POST submit edited data
        if(!empty($_POST['item_data']) && $item_being_edited != null) {
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
                $query .= "WHERE `item_id`='{$item_being_edited['item_id']}'";

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
        if($item_being_edited) {
            $data =& $item_being_edited;
            echo "<p style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
                <a href='$self_link' style='font-size:14px;'>Back to item select</a> 
            </p>            
            <table class='table'>
                <tr><th>Edit Item (" . stripslashes($item_being_edited['name']) . ")</th></tr>
                <tr><td>
                    <form action='$self_link&item_id={$item_being_edited['item_id']}' method='post'>";

            displayFormFields($variables, $data);

            echo "<br />
                    <input type='submit' name='item_data' value='Edit' />
                    </form>
                </td></tr>
			</table>";
        }

        // Show form for selecting ID
        if(!$item_being_edited) {
            $result = $system->query("SELECT * FROM `items`");
            $all_items = [];
            while($row = $system->db_fetch($result)) {
                $all_items[$row['item_id']] = Item::fromDb($row);
            }

            require 'templates/admin/edit_item_select.php';
        }
    }
    // Edit Bloodline
    else if($page == 'edit_bloodline') {
        $variables =& $constraints['bloodline'];
        $self_link = $system->router->getUrl('admin', ['page' => "edit_bloodline"]);

        // Validate NPC id
        $editing_bloodline_id = null;
        $bloodline_data = null;
        if(!empty($_GET['bloodline_id'])) {
            $editing_bloodline_id = (int)$system->clean($_GET['bloodline_id']);
            $result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id`='$editing_bloodline_id'");
            if($system->db_last_num_rows == 0) {
                $system->message("Invalid bloodline!");
                $system->printMessage();
                $editing_bloodline_id = null;
            }
            else {
                $bloodline_data = $system->db_fetch($result);
                $select_content = false;
            }
        }

        // POST submit edited data
        if(isset($_POST['bloodline_data']) && $editing_bloodline_id != null) {
            try {
                $data = [];

                validateFormData($variables, $data);

                $update_set_clauses = [];
                foreach($data as $name => $var) {
                    $update_set_clauses[] = "`$name` = '$var'";
                }

                $system->query("UPDATE `bloodlines` SET "
                    . implode(', ', $update_set_clauses)
                    . " WHERE `bloodline_id`='$editing_bloodline_id'");

                if($system->db_last_affected_rows == 1) {
                    $system->message('Bloodline ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $editing_bloodline_id = null;
            }
            $system->printMessage();
        }

        // Form for editing data
        if($bloodline_data && $editing_bloodline_id) {
            require 'templates/admin/edit_bloodline.php';
        }

        // Show form for selecting ID
        if($editing_bloodline_id == null) {
            $result = $system->query("SELECT * FROM `bloodlines` ORDER BY `rank` ASC");
            $all_bloodlines = [];
            while($row = $system->db_fetch($result)) {
                $all_bloodlines[$row['bloodline_id']] = new Bloodline($row);
            }

            require 'templates/admin/edit_bloodline_select.php';
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
            $editing_bloodline_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'");
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
                $query .= "WHERE `{$content_name}_id`='$editing_bloodline_id'";
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>";
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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
            $editing_bloodline_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'");
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
                $query .= "WHERE `{$content_name}_id`='$editing_bloodline_id'";
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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
            $editing_bloodline_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'");
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
                $query .= "WHERE `{$content_name}_id`='$editing_bloodline_id'";
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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
            $editing_bloodline_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'");
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
                $query .= "WHERE `{$content_name}_id`='$editing_bloodline_id'";
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name])}' method='post'>
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


    // Logs
    else if($page == 'logs') {
        $self_link .= "&page=logs";
        $default_view = 'staff_logs';
        $view = $default_view;

        //Pagination - log types
        if(isset($_GET['view'])) {
            $view = $system->clean($_GET['view']);
            if(!in_array($view, ['staff_logs', 'currency_logs'])) {
                $view = $default_view;
            }
            $self_link .= "&view=$view";
        }

        $offset = 0;
        $limit = 25;
        $max = $player->staff_manager->getStaffLogs($view, 'all', $offset, $limit, true) - $limit;

        if(isset($_GET['offset'])) {
            $offset = (int) $_GET['offset'];
            if($offset < 0) {
                $offset = 0;
            }
            if($offset > $max) {
                $offset = $max;
            }
        }
        $next = $offset + $limit;
        $previous = $offset - $limit;
        if($next > $max) {
            $next = $max;
        }
        if($previous < 0) {
            $previous = 0;
        }

        $logs = $player->staff_manager->getStaffLogs($view, 'all', $offset, $limit);

        if($system->message) {
            $system->printMessage();
        }
        require 'templates/admin/logs.php';
    }

    /* USER ADMINISTRATION PAGES */
    else if($page == 'edit_user') {
        require 'admin/user.php';
        editUserPage($system, $player);
    }
    // Activate user
    else if($page == 'activate_user') {
        require 'admin/user.php';
        activateUserPage($system, $player);
    }
    // Delete user
    else if($page == 'delete_user') {
        require 'admin/user.php';
        deleteUserPage($system, $player);
    }
    // Give bloodline
    else if($page == 'give_bloodline') {
        require 'admin/user.php';
        giveBloodlinePage($system);
    }
    // Stat cut
    else if($page == 'stat_cut') {
        require 'admin/user.php';
        statCutPage($system, $player);
    }
    else if($page == 'dev_tools') {
        require 'admin/user.php';
        devToolsPage($system, $player);
    }

}

