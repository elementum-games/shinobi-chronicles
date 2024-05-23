<?php /** @noinspection SqlInsertValues */

require_once 'admin/formTools.php';

function createBloodlinePage($system) {
    /* Variables */
    $bloodline_constraints = require 'admin/constraints/bloodline.php';
    $error = false;
    $data = [];

    if(isset($_POST['bloodline_data'])) {
        try {
            $data = [];

            $FORM_DATA = $_POST;

            $FORM_DATA['passive_boosts'] = array_filter($FORM_DATA['passive_boosts'], function ($boost) {
                return $boost['effect'] !== 'none';
            });
            $FORM_DATA['combat_boosts'] = array_filter($FORM_DATA['combat_boosts'], function ($boost) {
                return $boost['effect'] !== 'none';
            });
            $FORM_DATA['jutsu'] = array_filter($FORM_DATA['jutsu'], function ($jutsu) {
                return $jutsu['name'] !== '';
            });

            validateFormData(
                entity_constraints: $bloodline_constraints,
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

            $query = "INSERT INTO `bloodlines` ($column_names) VALUES ($column_data)";
            $system->db->query($query);

            if($system->db->last_affected_rows == 1) {
                $system->message("Bloodline created!");
            }
            else {
                throw new RuntimeException("Error creating bloodline!");
            }
        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
            $error = true;
        }
        $system->printMessage();
    }

    if($error) {
        foreach($bloodline_constraints as $var_name => $variable) {
            if(isset($_POST[$var_name])) {
                $data[$var_name] = htmlspecialchars($_POST[$var_name], ENT_QUOTES);
            }
            else {
                $data[$var_name] = '';
            }
        }
    }
    else {
        foreach($bloodline_constraints as $var_name => $variable) {
            $data[$var_name] = '';
        }
    }

    $form_action_url = ($system->USE_ROUTE_V2) ? $system->routerV2->current_route : $system->router->getUrl('admin', ['page' => 'create_bloodline']);
    require 'templates/admin/bloodline_form.php';
}

function editBloodlinePage($system) {
    /* Variables */
    $bloodline_constraints = require 'admin/constraints/bloodline.php';

    $self_link = $system->router->getUrl('admin', ['view' => "edit_bloodline"]);

    // Validate NPC id
    $editing_bloodline_id = null;
    $existing_bloodline = null;
    if(!empty($_GET['bloodline_id'])) {
        $editing_bloodline_id = (int)$system->db->clean($_GET['bloodline_id']);

        try {
            $existing_bloodline = Bloodline::loadFromId($system, $editing_bloodline_id);
        } catch(RuntimeException $e) {
            $system->message("Invalid bloodline!");
            $system->printMessage();
            $editing_bloodline_id = null;
        }
    }

    // POST submit edited data
    if(isset($_POST['bloodline_data']) && $editing_bloodline_id != null && $existing_bloodline != null) {
        try {
            $data = [];

            $FORM_DATA = $_POST;

            $FORM_DATA['passive_boosts'] = array_filter($FORM_DATA['passive_boosts'], function ($boost) {
                return $boost['effect'] !== 'none';
            });
            $FORM_DATA['combat_boosts'] = array_filter($FORM_DATA['combat_boosts'], function ($boost) {
                return $boost['effect'] !== 'none';
            });
            $FORM_DATA['jutsu'] = array_filter($FORM_DATA['jutsu'], function ($jutsu) {
                return $jutsu['name'] !== '';
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
    if($existing_bloodline != null && $editing_bloodline_id != null) {
        if($system->USE_ROUTE_V2) {
            $system->routerV2->setCurrentRoute(var_name: 'bloodline_id', value: $existing_bloodline->bloodline_id);
            $form_action_url = $system->routerV2->current_route;
        }
        else {
            $form_action_url = $system->router->getUrl('admin', [
                'page' => 'edit_bloodline', 'bloodline_id'=> $existing_bloodline->bloodline_id
            ]);
        }
        require 'templates/admin/bloodline_form.php';
    }

    // Show form for selecting ID
    if($editing_bloodline_id == null) {
        $result = $system->db->query("SELECT * FROM `bloodlines` ORDER BY `rank` ASC");
        $all_bloodlines = [];
        while($row = $system->db->fetch($result)) {
            $all_bloodlines[$row['bloodline_id']] = Bloodline::fromArray($row);
        }

        require 'templates/admin/edit_bloodline_select.php';
    }
}
