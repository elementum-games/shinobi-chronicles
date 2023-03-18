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

    // Staff level check
    if(!$player->hasAdminPanel()) {
        return false;
    }

    $admin_panel_url = $system->getUrl('admin');

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
            array_map(function($page_slug) use ($admin_panel_url) {
                return "<a href='{$admin_panel_url}&page={$page_slug}'>" . System::unSlug($page_slug) . "</a>";
            }, $content_create_pages)
        );
        echo "</td></tr>";
    }
    if($player->isContentAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($admin_panel_url) {
                return "<a href='{$admin_panel_url}&page={$page_slug}'>" . System::unSlug($page_slug) . "</a>";
            }, $content_edit_pages)
        );
        echo "</td></tr>";
    }
    if($player->isUserAdmin()) {
        echo "<tr><td style='text-align:center'>";
        echo implode(
            "&nbsp;&nbsp;|&nbsp;&nbsp;",
            array_map(function($page_slug) use ($admin_panel_url) {
                return "<a href='{$admin_panel_url}&page={$page_slug}'>" . System::unSlug($page_slug) . "</a>";
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
		<form action='$admin_panel_url&page=create_ai' method='post'>";
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
		<form action='$admin_panel_url&page=create_jutsu' method='post'>";
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
		<form action='$admin_panel_url&page=create_item' method='post'>";
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
		<form action='$admin_panel_url&page=create_" . $content_name . "' method='post'>";
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
		<form action='$admin_panel_url&page=create_" . $content_name . "' method='post'>";
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
		<form action='$admin_panel_url&page=create_" . $content_name . "' method='post'>";
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
		<form action='$admin_panel_url&page=create_" . $content_name . "' method='post'>";
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
        $self_link = $admin_panel_url . '&page=edit_ai';

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
			<form action='{$admin_panel_url}&page=edit_ai&npc_id={$ai_data['ai_id']}' method='post'>";
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
    // Edit jutsu
    else if($page == 'edit_jutsu') {
        $select_jutsu = true;
        $self_link = $admin_panel_url . '&page=edit_jutsu';

        /* Variables */
        $variables =& $constraints['jutsu'];

        // Validate jutsu id
        $jutsu_id = null;
        $jutsu_data = null;
        if(!empty($_GET['jutsu_id'])) {
            $jutsu_id = (int)$_GET['jutsu_id'];

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
        if(!empty($_POST['jutsu_data']) && !$select_jutsu) {
            try {
                $editing_bloodline_id = $jutsu_id;
                $data = [];
                validateFormData($variables, $data, $editing_bloodline_id);

                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;

                $query = "UPDATE `jutsu` SET";
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
            echo "<p style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
                <a href='$self_link' style='font-size:14px;'>Back to jutsu list</a>
            </p>
            <table class='table'>
                <tr><th>Edit Jutsu (" . stripslashes($jutsu_data['name']) . ")</th></tr>
                <tr><td>
                <form action='$self_link&jutsu_id={$jutsu_data['jutsu_id']}' method='post'>
                <label>Jutsu ID:</label> $jutsu_id<br />";
            displayFormFields($variables, $data);
            echo "<br />
                <input type='submit' name='jutsu_data' value='Edit' />
                </form>
                </td></tr>
			</table>";
        }

        // Show form for selecting ID
        if($select_jutsu) {
            $all_jutsu = [];
            $result = $system->query("SELECT * FROM `jutsu` ORDER BY `rank` ASC, `purchase_cost` ASC");
            while($row = $system->db_fetch($result)) {
                $all_jutsu[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
            }

            require 'templates/admin/edit_jutsu_select.php';
        }
    }
    // Edit item
    else if($page == 'edit_item') {
        $item_being_edited = null;
        $table_name = 'items';
        $self_link = $admin_panel_url . '&page=edit_item';

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
        $self_link = $admin_panel_url . '&page=edit_bloodline';

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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>";
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_{$content_name}' method='post'>
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
			<form action='$admin_panel_url&page=edit_user&user_name={$data['user_name']}' method='post'>";
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
			<form action='$admin_panel_url&page=edit_user' method='get'>
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
			<form action='$admin_panel_url&page=activate_user' method='post'>
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
						<form action='$admin_panel_url&page=delete_user' method='post'>
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
				<form action='$admin_panel_url&page=delete_user' method='post'>
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
            $editing_bloodline_id = (int)$system->clean($_POST['bloodline_id']);
            $user_name = $system->clean($_POST['user_name']);
            try {
                if(!isset($bloodlines[$editing_bloodline_id])) {
                    throw new Exception("Invalid bloodline!");
                }
                $result = $system->query("SELECT `user_id` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
                if($system->db_last_num_rows == 0) {
                    throw new Exception("User does not exist!");
                }
                $result = $system->db_fetch($result);
                $user_id = $result['user_id'];
                $status = Bloodline::giveBloodline(
                    system: $system,
                    bloodline_id: $editing_bloodline_id,
                    user_id: $user_id
                );
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        echo "<table class='table'><tr><th>Give Bloodline</th></tr>
		<tr><td>
		<form action='$admin_panel_url&page=give_bloodline' method='post'>
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
    // Logs
    else if($page == 'logs') {
        $self_link .= "&page=logs";
        $default_view = 'staff_logs';
        $view = $default_view;

        //Pagination - log types
        if(isset($_GET['view'])) {
            $view = $system->clean($_GET['view']);
            if(!in_array($view, ['staff_logs', 'currency_logs', 'player_logs'])) {
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
    // Stat cut
    else if($page == 'stat_cut') {
        $self_link .= '&page=stat_cut';
        require_once 'classes/RankManager.php';
        $rankManager = new RankManager($system);
        $rankManager->loadRanks();
        $user = false;

        if(isset($_POST['set_user'])) {
            try {
                $name = $system->clean($_POST['user_name']);
                $user = $player->staff_manager->getUserByName($name, true);
                if(!$user) {
                    throw new Exception("Invalid user!");
                }
            } catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
        if(isset($_POST['cut_stats'])) {
            try {
                $user_id = $system->clean($_POST['user_id']);
                $cut_amount = round(1 - ($_POST['cut_amount'] / 100), 2);
                $cut_ai = isset($_POST['cut_ai']);
                $cut_pvp = isset($_POST['cut_pvp']);
                $cut_yen = isset($_POST['cut_yen']);

                $skills_to_cut = [
                    'rank', 'level', 'exp', 'ai_wins', 'pvp_wins', 'money',
                    'health', 'max_health', 'chakra', 'max_chakra', 'stamina', 'max_stamina',
                    'ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'bloodline_skill',
                    'cast_speed', 'speed', 'intelligence', 'willpower'
                ];

                if(!$cut_ai) {
                    unset($skills_to_cut[array_search('ai_wins', $skills_to_cut)]);
                }
                if(!$cut_pvp) {
                    unset($skills_to_cut[array_search('pvp_wins', $skills_to_cut)]);
                }
                if(!$cut_yen) {
                    unset($skills_to_cut[array_search('money', $skills_to_cut)]);
                }

                $user = $player->staff_manager->getUserByID($user_id, true);
                if(!$user) {
                    throw new Exception("Invalid user!");
                }

                if($user['user_id'] == $player->user_id) {
                    $user = false;
                    throw new Exception("You can not cut your own stats!");
                }

                //New data
                $total_stats = 0;
                $new_data = array();
                foreach($skills_to_cut as $skill) {
                    if($skill == 'level' || $skill =='rank') {
                        continue;
                    }
                    else {
                        $new_data[$skill] = floor($user[$skill] * $cut_amount);
                    }
                    if(in_array($skill, ['ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'bloodline_skill',
                        'intelligence', 'willpower', 'speed', 'cast_speed'])) {
                        $total_stats += $new_data[$skill];
                    }
                }

                //Rank & level
                $new_data['rank'] = $rankManager->calculateRankFromTotalStats($total_stats);
                $new_data['level'] = $rankManager->calculateMaxLevel($total_stats, $new_data['rank']);

                //Elements
                $max_elements = 0;
                if($new_data['rank'] >= 3) {
                    $max_elements++;
                }
                if($new_data['rank'] >= 4) {
                    $max_elements++;
                }
                $user['elements'] = json_decode($user['elements'], true);
                $element_count = count($user['elements']);

                if($element_count > $max_elements) {
                    $skills_to_cut[] = 'elements';
                    if($max_elements == 0) {
                        $new_data['elements'] = json_encode(array());
                    }
                    elseif($max_elements == 1) {
                        $new_data['elements'] = json_encode(['first' => $user['elements']['first']]);
                    }
                }
                $user['elements'] = json_encode($user['elements']); // Set back to string for debugging display

                //Pools & health
                $health = $rankManager->healthForRankAndLevel($new_data['rank'], $new_data['level']);
                $pools = $rankManager->chakraForRankAndLevel($new_data['rank'], $new_data['level']);

                $new_data['health'] = 0;
                $new_data['max_health'] = $health;
                $new_data['max_chakra'] = $pools;
                $new_data['max_stamina'] = $pools;
                $new_data['chakra'] = 0;
                $new_data['stamina'] = 0;

                //Debug
                if($system->debug['stat_cut']) {
                    echo "<div style='width:75%;margin:1rem auto;border:1px solid red;'>
                        <label>UID:</label>$user_id<br />
                        <label>Total Sstats:</label>$total_stats<br /><br />";

                    array_map(function($skill) use ($user, $new_data) {
                        echo "<label>" . System::unSlug($skill) . ":</label>{$user[$skill]} => {$new_data[$skill]}<br />";
                    }, $skills_to_cut);

                    echo "</div>";

                    throw new Exception("Debugging!");
                }

                $query = "UPDATE `users` SET ";
                $log_data = "";
                foreach($skills_to_cut as $skill) {
                    $log_data .= "$skill: {$user[$skill]} => {$new_data[$skill]}
                    ";
                    $query .= "`" . $skill . "`='{$new_data[$skill]}', ";
                }
                $query = substr($query, 0, strlen($query)-2) . " WHERE `user_id`='$user_id' LIMIT 1";
                $system->query($query);
                if ($system->db_last_affected_rows) {
                    $system->message("{$user['user_name']} has had stats cut!");
                    $player->staff_manager->staffLog(StaffManager::STAFF_LOG_ADMIN, "{$player->user_name}({$player->user_id})"
                        . " cut {$user['user_name']}\'s({$user['user_id']}) by " . 100 - ($cut_amount * 100) . "%.
                        
                        " . $log_data);
                    $user = false;
                }
                else {
                    $system->message("Error cutting stats.");
                }
            } catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }

        if($system->message) {
            $system->printMessage();
        }
        require 'templates/admin/stat_cut.php';
    }
    else if($page == 'dev_tools') {
        $stats = [
            'ninjutsu_skill',
            'taijutsu_skill',
            'genjutsu_skill',
            'bloodline_skill',
            'cast_speed',
            'speed',
        ];

        if (!empty($_POST['cap_jutsu'])) {
            $name = $system->clean($_POST['cap_jutsu']);

            try {
                $user = User::findByName($system, $name);
                if($user == null) {
                    throw new Exception("Invalid user!");
                }

                $user->loadData(UPDATE: User::UPDATE_NOTHING, remote_view: true);
                $user->getInventory();

                //Content admin restriction
                if(!$player->isHeadAdmin() && $user->user_id != $player->user_id) {
                    throw new Exception("You may only edit your own characters!");
                }

                foreach($user->jutsu as &$jutsu) {
                    $jutsu->level = 100;
                    $jutsu->exp = 0;
                }
                unset($jutsu);

                if($user->bloodline != null && count($user->bloodline->jutsu) > 0) {
                    foreach($user->bloodline->jutsu as &$jutsu) {
                        $jutsu->level = 100;
                        $jutsu->exp = 0;
                    }
                    unset($jutsu);
                }

                $system->log(
                    'admin',
                    'capped jutsu',
                    "Admin {$user->user_name} (#{$user->user_id}) capped jutsu for player {$user->user_name} (#{$user->user_id})"
                );
                $user->updateInventory();

                $system->message("Jutsu capped for {$user->user_name}.");
            } catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
        else if (!empty($_POST['cap_stats'])) {
            $name = $system->clean($_POST['user']);
            $selected_rank = $_POST['rank'];

            //Content admin constraints
            try {
                $user = User::findByName($system, $name);
                if($user == null) {
                    throw new Exception("Invalid user!");
                }

                if(!$player->isHeadAdmin() && $user->user_id != $player->user_id) {
                    throw new Exception("You may only edit your own characters!");
                }

                $user->loadData(User::UPDATE_NOTHING);

                $rankManager = new RankManager($system);
                $rankManager->loadRanks();

                if($selected_rank == 'current') {
                    $selected_rank = $user->rank_num;
                }

                $rank = $rankManager->ranks[$selected_rank];
                $total_stats = $rank->stat_cap;

                foreach($stats as $stat) {
                    if(!empty($_POST[$stat . '_percent'])) {
                        $percent = $_POST[$stat . '_percent'];
                        $amount = $percent * $total_stats;

                        echo "{$stat} to {$percent}x of rank {$rank->name} cap ({$amount})<br />";
                        $user->$stat = $amount;

                        if($user->user_id == $player->user_id) {
                            $player->$stat = $amount;
                        }
                    }
                }

                $user->exp = $total_stats * 10;

                $user->updateData();

                $system->log(
                    'admin',
                    'capped jutsu',
                    "Admin {$user->user_name} (#{$user->user_id}) capped stats for player {$user->user_name} (#{$user->user_id})"
                );
                $system->message("Stats capped for $name.");
            } catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }

        require 'templates/admin/dev_tools.php';
    }

}

