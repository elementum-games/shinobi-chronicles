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
 * @throws RuntimeException
 */
function adminPanel() {
    global $system;
    global $player;
    global $id;
    global $self_link;

    // Staff level check
    if(!$player->staff_manager->hasAdminPanel()) {
        return false;
    }

    require 'templates/admin/panel_menu.php';

    // Variable sets
    $constraints = require 'admin/entity_constraints.php';

    $page = $_GET['page'] ?? '';

    if(in_array($page, $player->staff_manager->getAdminPanelPerms(type: 'misc_tools', permission_check: true)) && !$player->isUserAdmin()) {
        $page = '';
    }
    else if(in_array($page, $player->staff_manager->getAdminPanelPerms(type: 'create_content', permission_check: true)) && !$player->isContentAdmin()) {
        $page = '';
    }
    else if(in_array($page, $player->staff_manager->getAdminPanelPerms(type: 'edit_content', permission_check: true)) && !$player->isContentAdmin()) {
        $page = '';
    }

    // Open server from maintenance
    if($page== 'server_maint') {
        $self_link .= "&page=server_maint";
		
		// Open server
        if(isset($_POST['open_sc'])) {
            $system->db->query("UPDATE `system_storage` SET `maintenance_end_time`=0, `maintenance_begin_time`=0 LIMIT 1");
            if($system->db->last_affected_rows) {
				$system->UPDATE_MAINTENANCE = null;
				$system->SC_OPEN = true;
                $system->message("Server opened!");
            }
            else {
                $system->message("Error opening server!");
            }
        }
		// Start maintenance
		if(isset($_POST['start_maint'])) {
			try {
				$allowed_types = ['min', 'hour'];
				$begin_type = $_POST['begin_type'];
				$end_type = $_POST['end_type'];

				$begin_time = (int) $_POST['begin_time'];
				$end_time = (int) $_POST['end_time'];
				
				if(!in_array($begin_type, $allowed_types)) {
					throw new RuntimeException("Invalid begin type!");
				}
				if(!in_array($end_type, $allowed_types)) {
					throw new RuntimeException("Invalid end type!");
				}
				
				$begin_time_multiplier = ($begin_type == 'min') ? 60 : 3600;
				$end_time_multiplier = ($end_type == 'min') ? 60 : 3600;
				
				$begin_seconds = $begin_time * $begin_time_multiplier;
				$end_seconds = $end_time * $end_time_multiplier;
				
				// Server countdown must be at least 5 minutes
				if($begin_seconds < 300) {
					throw new RuntimeException("You must allow at least 5 minutes prior to maintenance period!");
				}
				// Downtime must be at least 5 minutes
				if($end_seconds < 300) {
					throw new RuntimeException("Server must remain closed for at least 5 minutes!");
				}
				
				$TIME = new DateTimeImmutable('now', new DateTimezone(System::SERVER_TIME_ZONE));
				
				$BEGIN_TIME = $TIME->setTimestamp($TIME->getTimestamp() + $begin_seconds);
				$END_TIME = $TIME->setTimestamp($BEGIN_TIME->getTimestamp() + $end_seconds);
				
				$system->db->query("UPDATE `system_storage` SET `maintenance_begin_time`='{$BEGIN_TIME->getTimestamp()}', `maintenance_end_time`='{$END_TIME->getTimestamp()}' LIMIT 1");
				
				if($system->db->last_affected_rows) {
					$system->UPDATE_MAINTENANCE = $BEGIN_TIME;
					$system->message("Maintenance started!");
				}
				else {
					$system->message("Error beginning maintenance!");
				}
			}catch(RuntimeException $e) {
				$system->message($e->getMessage());
			}
		}
		// Hard close server
		if(isset($_POST['close_server'])) {
			$system->db->query("UPDATE `system_storage` SET `maintenance_end_time`=-1 LIMIT 1");
			if($system->db->last_affected_rows) {
				$system->SC_OPEN = false;
				$system->message("Server closed!");
			}
			else {
				$system->message("Error closing server!");
			}
		}

        $system->printMessage();
        require 'templates/admin/sc_maint.php';
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
        if(isset($_POST['ai_data'])) {
            try {
                $data = [];
				
                $FORM_DATA = $_POST;
		    	$FORM_DATA['moves'] = array_filter($FORM_DATA['moves'], function ($moves) {
					return $moves['battle_text'] !== '';
            	});
                validateFormData(
	                entity_constraints: $variables,
	                data: $data,
	                FORM_DATA: $FORM_DATA
            	);
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message("NPC created!");
                }
                else {
                    throw new RuntimeException("Error creating NPC!");
                }
            } catch(RuntimeException $e) {
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
    else if($page == 'edit_ai') {
        /* Variables */
        $variables =& $constraints['ai'];
        $select_ai = true;
        $self_link = $system->router->getUrl('admin', ['page' => "edit_ai"]);

        // Validate NPC id
        if(!empty($_GET['npc_id'])) {
            $npc_id = (int)$system->db->clean($_GET['npc_id']);
            $result = $system->db->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$npc_id'");
            if($system->db->last_num_rows == 0) {
                $system->message("Invalid NPC!");
                $system->printMessage();
            }
            else {
                $ai_data = $system->db->fetch($result);
                $select_ai = false;
            }
        }

        // POST submit edited data
        if(!empty($_POST['ai_data']) && !$select_ai) {
            try {
                $data = [];
				
                $FORM_DATA = $_POST;
		    	$FORM_DATA['moves'] = array_filter($FORM_DATA['moves'], function ($moves) {
					return $moves['battle_text'] !== '';
            	});
                validateFormData(
	                entity_constraints: $variables,
	                data: $data,
	                FORM_DATA: $FORM_DATA
            	);
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message("NPC " . $data['name'] . " has been edited!");
                    $select_ai = true;
                }
                else {
                    throw new RuntimeException("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(RuntimeException $e) {
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
            $result = $system->db->query("SELECT * FROM `ai_opponents` ORDER BY `level` ASC");
            while($row = $system->db->fetch($result)) {
                $all_npcs[$row['ai_id']] = $row;
            }

            require 'templates/admin/edit_npc_select.php';
        }
    }

    // jutsu
    else if($page == 'create_jutsu') {
        require 'admin/jutsu.php';
        createJutsuPage($system);
    }
    else if($page == 'edit_jutsu') {
        require 'admin/jutsu.php';
        editJutsuPage($system);
    }

    // Item
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
        if(isset($_POST['item_data'])) {
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message("Item created!");
                }
                else {
                    throw new RuntimeException("Error creating item!");
                }
            } catch(RuntimeException $e) {
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
		<form action='{$system->router->getUrl('admin', ['page' => 'create_item'])}' method='post'>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='item_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    else if($page == 'edit_item') {
        $item_being_edited = null;
        $select_item = true;
        $table_name = 'items';
        $self_link = $system->router->getUrl('admin', ['page' => "edit_item"]);

        /* Variables */
        $variables =& $constraints['item'];

        // Validate item id
        if(!empty($_GET['item_id'])) {
            $item_id = (int)$_GET['item_id'];

            $result = $system->db->query("SELECT * FROM `$table_name` WHERE `item_id`='$item_id'");
            if($system->db->last_num_rows == 0) {
                $system->message("Invalid item!");
                $system->printMessage();
            }
            else {
                $select_item = false;
                $item_being_edited = $system->db->fetch($result);
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
                $system->db->query($query);

                if($system->db->last_affected_rows == 1) {
                    $system->message("Item edited!");
                    $select_item = true;
                }
                else {
                    throw new RuntimeException("Error editing item!");
                }
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }

        // Form for editing data
        if($item_being_edited && !$select_item) {
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
        if($select_item) {
            $result = $system->db->query("SELECT * FROM `items`");
            $all_items = [];
            while($row = $system->db->fetch($result)) {
                $all_items[$row['item_id']] = Item::fromDb($row);
            }

            require 'templates/admin/edit_item_select.php';
        }
    }

    // Bloodline
    else if($page == 'create_bloodline') {
        require 'admin/bloodline.php';
        createBloodlinePage($system);
    }
    else if($page == 'edit_bloodline') {
        require 'admin/bloodline.php';
        editBloodlinePage($system);
    }

    // Create rank
    else if($page == 'create_rank') {
        $table_name = 'ranks';
        $content_name = 'rank';
        /* Variables */
        $variables =& $constraints['rank'];
        $error = false;
        $data = [];
        if(isset($_POST[$content_name . '_data'])) {
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new RuntimeException("Error creating " . $content_name . "!");
                }
            } catch(RuntimeException $e) {
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
    else if($page == 'edit_rank') {
        $table_name = 'ranks';
        $content_name = 'rank';
        /* Variables */
        $variables =& $constraints['rank'];
        $select_content = true;
        // Validate content id
        if(isset($_GET['rank_id'])) {
            $rank_id = (int) $_GET['rank_id'];
            $result = $system->db->query("SELECT * FROM `{$table_name}` WHERE `rank_id` = '{$rank_id}'");
            if($system->db->last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db->fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if(isset($_POST[$content_name . '_data']) && !$select_content) {
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
                $query .= "WHERE `{$content_name}_id`='$rank_id'";
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new RuntimeException("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if(isset($content_data) && !$select_content) {
            require 'templates/admin/edit_rank.php';
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->db->query("SELECT * FROM `$table_name`");
            $ranks = [];
            while($row = $system->db->fetch($result)) {
                $ranks[] = $row;
            }
            require 'templates/admin/edit_rank_select.php';
        }
    }

    // Create Clan
    else if($page == 'create_clan') {
        $table_name = 'clans';
        $content_name = 'clan';
        /* Variables */
        $variables =& $constraints['create_clan'];
        $error = false;
        $data = [];
        if(isset($_POST[$content_name . '_data'])) {
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new RuntimeException("Error creating " . $content_name . "!");
                }
            } catch(RuntimeException $e) {
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
    else if($page == 'edit_clan') {
        $table_name = 'clans';
        $content_name = 'clan';
        /* Variables */
        $variables =& $constraints['edit_clan'];

        $select_content = true;
        // Validate NPC id
        if(isset($_GET[$content_name . '_id'])) {
            $editing_bloodline_id = (int) $_GET[$content_name . '_id'];
            $result = $system->db->query(
                "SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'"
            );
            if($system->db->last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db->fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if(isset($_POST[$content_name . '_data']) && !$select_content) {
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new RuntimeException("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if(isset($content_data) && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name, 'clan_id' => $content_data[$content_name . '_id']])}' method='post'>
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
            $clans = array();
            $result = $system->db->query("SELECT `{$content_name}_id`, `name`, `village`, `bloodline_only` FROM 
                `$table_name` ORDER BY `village` ASC, `bloodline_only` ASC");
            if($system->db->last_num_rows) {
                while($row = $system->db->fetch($result)) {
                    $clans[] = $row;
                }
            }
            require 'templates/admin/edit_clan_select.php';
        }
    }

    // Mission
    else if($page == 'create_mission') {
        $table_name = 'missions';
        $content_name = 'mission';
        /* Variables */
        $mission_constraints =& $constraints['mission'];
        $error = false;
        $data = [];
        if(isset($_POST[$content_name . '_data'])) {
            try {
				$data = [];
				
		    	$FORM_DATA = $_POST;
		    	$FORM_DATA['stages'] = array_filter($FORM_DATA['stages'], function ($stage) {
					return isset($stage['action_type']);
            	});
				$FORM_DATA['rewards'] = array_filter($FORM_DATA['rewards'], function ($rewards) {
					return $rewards['item_id'] !== '';
            	});

                validateFormData(
	                entity_constraints: $mission_constraints,
	                data: $data,
	                FORM_DATA: $FORM_DATA
            	);

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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new RuntimeException("Error creating " . $content_name . "!");
                }
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($mission_constraints, $data);
        }
        else {
            formPreloadData($mission_constraints, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='{$system->router->getUrl('admin', ['page' => 'create_' . $content_name])}' method='post'>";
        displayFormFields($mission_constraints, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    else if($page == 'edit_mission') {
        $table_name = 'missions';
        $content_name = 'mission';
        /* Variables */
        $mission_constraints =& $constraints['mission'];

        $select_content = true;
        // Validate content id
        if(isset($_GET[$content_name . '_id'])) {
            $editing_bloodline_id = (int)$_GET[$content_name . '_id'];
            $result = $system->db->query(
                "SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'"
            );
            if($system->db->last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db->fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if(isset($_POST[$content_name . '_data']) && !$select_content) {
            try {
				$data = [];
				
                $FORM_DATA = $_POST;
		    	$FORM_DATA['stages'] = array_filter($FORM_DATA['stages'], function ($stage) {
					return isset($stage['action_type']);
            	});
				$FORM_DATA['rewards'] = array_filter($FORM_DATA['rewards'], function ($rewards) {
					return $rewards['item_id'] !== '';
            	});

                validateFormData(
	                entity_constraints: $mission_constraints,
	                data: $data,
	                FORM_DATA: $FORM_DATA
            	);
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new RuntimeException("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if(isset($content_data) && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_' . $content_name, 'mission_id' => $content_data[$content_name . '_id']])}' method='post'>
			<label>Mission ID:</label> " . $content_data['mission_id'] . "<br />";
            displayFormFields($mission_constraints, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $missions = array();
            $result = $system->db->query("SELECT * FROM `$table_name` ORDER BY `rank` DESC");
            if($system->db->last_num_rows) {
                while($mission = $system->db->fetch($result)) {
                    // Format stages
                    $stages = json_decode($mission['stages'], true);
                    $stage_display = '';
                    foreach($stages as $stage_id => $stage_data) {
                        $stage_display .= "<b>Step " . $stage_id+1 . "</b><br />
                        Action Type: {$stage_data['action_type']}<br />";
                    }
                    $mission['stages'] = $stage_display;

                    // Format rewards
                    $rewards = json_decode($mission['rewards'], true);
                    $rewards_display = 'None';
                    if(!empty($rewards)) {
                        $rewards_display = '';
                        foreach($rewards as $reward_num => $reward) {
                            $rewards_display .= "<b>Reward " . $reward_num+1 . "</b><br />";
                            foreach($reward as $reward_name => $reward_data) {
                                $rewards_display .= "$reward_name: $reward_data<br />";
                            }
                            $rewards_display .= "<br />";
                        }
                    }
                    $mission['rewards'] = $rewards_display;


                    $missions[] = $mission;
                }
            }
            require 'templates/admin/edit_mission_select.php';
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
        if(isset($_GET[$content_name . '_id'])) {
            $editing_bloodline_id = (int)$_GET[$content_name . '_id'];
            $result = $system->db->query(
                "SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$editing_bloodline_id'"
            );
            if($system->db->last_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db->fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if(isset($_POST[$content_name . '_data']) && !$select_content) {
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
                $system->db->query($query);
                if($system->db->last_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new RuntimeException("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if(isset($content_data) && !$select_content) {
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
            $result = $system->db->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td style='text-align: center;'>
			<form action='{$system->router->getUrl('admin')}' method='get'>
			    <input type='hidden' name='id' value='" . Router::PAGE_IDS['admin'] . "' />
			    <input type='hidden' name='page' value='edit_{$content_name}' />
			    Team ID: <input type='text' name='{$content_name}_id' />
			    <input type='submit' /><br />
			    <em>Note: Team edit link can be found on user profiles</em>
			</form>
			</td></tr></table>";
        }
    }

    // Logs
    else if($page == 'logs') {
       require 'admin/logs.php';
       viewLogsPage($system, $player);
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
    // Reset password
    else if($page == 'reset_password') {
        require 'admin/user.php';
        resetPasswordPage($system, $player);
    }
    // Give bloodline
    else if($page == 'give_bloodline') {
        require 'admin/user.php';
        giveBloodlinePage($system, $player);
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
    else if($page == 'staff_payments') {
        require 'admin/user.php';
        StaffPaymentPage($system, $player);
    }
    else if($page == 'manual_transaction') {
        require 'admin/user.php';
        ManualCurrency($system, $player);
    }
}

