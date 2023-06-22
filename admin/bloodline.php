<?php /** @noinspection SqlInsertValues */

require_once 'admin/formTools.php';

function createBloodlinePage($system, $RANK_NAMES) {
    /* Variables */
    $bloodline_constraints = require 'admin/constraints/bloodline.php';

    $table_name = 'bloodlines';
    $content_name = 'bloodline';

    $error = false;
    $data = [];
    if($_POST[$content_name . '_data']) {
        try {
            $data = [];
            validateFormData($bloodline_constraints, $data);
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
        formPreloadData($bloodline_constraints, $data);
    }
    else {
        formPreloadData($bloodline_constraints, $data, false);
    }
    echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='" . $system->router->getUrl('admin', ['page' => "create_{$content_name}"]) . "' method='post'>";
        displayFormFields($bloodline_constraints, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
}

function editBloodlinePage($system, $RANK_NAMES) {
    /* Variables */
    $bloodline_constraints = require 'admin/constraints/bloodline.php';

    $self_link = $system->router->getUrl('admin', ['page' => "edit_bloodline"]);

    // Validate NPC id
    $editing_bloodline_id = null;
    $bloodline = null;
    if(!empty($_GET['bloodline_id'])) {
        $editing_bloodline_id = (int)$system->db->clean($_GET['bloodline_id']);

        try {
            $bloodline = Bloodline::loadFromId($system, $editing_bloodline_id);
        } catch(RuntimeException $e) {
            $system->message("Invalid bloodline!");
            $system->printMessage();
            $editing_bloodline_id = null;
        }
    }

    // POST submit edited data
    if(isset($_POST['bloodline_data']) && $editing_bloodline_id != null && $bloodline != null) {
        try {
            $data = [];

            $FORM_DATA = $_POST;

            $FORM_DATA['passive_boosts'] = array_filter($FORM_DATA['passive_boosts'], function ($boost) {
                return $boost['effect'] !== 'none';
            });
            $FORM_DATA['combat_boosts'] = array_filter($FORM_DATA['combat_boosts'], function ($boost) {
                return $boost['effect'] !== 'none';
            });

            validateFormData(entity_constraints: $bloodline_constraints, data: $data, FORM_DATA: $FORM_DATA);
            
            $update_set_clauses = [];
            foreach($data as $name => $var) {
                $update_set_clauses[] = "`$name` = '$var'";
            }

            $system->db->query(
                "UPDATE `bloodlines` SET "
                . implode(', ', $update_set_clauses)
                . " WHERE `bloodline_id`='$editing_bloodline_id'"
            );

            if($system->db->last_affected_rows == 1) {
                $system->message('Bloodline ' . $data['name'] . " has been edited!");
                $editing_bloodline_id = null;
            }
            else {
                throw new RuntimeException("Error editing " . $data['name'] . "! (Or data is the same)");
            }
        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
            $editing_bloodline_id = null;
        }
        $system->printMessage();
    }

    // Form for editing data
    if($bloodline != null && $editing_bloodline_id != null) {
        require 'templates/admin/edit_bloodline.php';
    }

    // Show form for selecting ID
    if($editing_bloodline_id == null) {
        $result = $system->db->query("SELECT * FROM `bloodlines` ORDER BY `rank` ASC");
        $all_bloodlines = [];
        while($row = $system->db->fetch($result)) {
            $all_bloodlines[$row['bloodline_id']] = new Bloodline($row);
        }

        require 'templates/admin/edit_bloodline_select.php';
    }
}