<?php /** @noinspection SqlInsertValues */

require_once 'admin/formTools.php';

function createJutsuPage(System $system, array $RANK_NAMES) {
    /* Variables */
    $jutsu_constraints = require 'admin/constraints/jutsu.php';
    $error = false;
    $data = [];

    $ALL_JUTSU = Jutsu::fetchAll($system);

    if(isset($_POST['jutsu_data'])) {
        try {
            $form_data = $_POST;

            // We will manually validate hand seals because they need custom rules
            unset($form_data['hand_seals']);

            $data = [];
            validateFormData(
                entity_constraints: $jutsu_constraints,
                data: $data,
                FORM_DATA: $form_data
            );

            // Do manual hand seal validation
            if($form_data['jutsu_type'] == Jutsu::TYPE_TAIJUTSU) {
                $data['hand_seals'] = "";
            }
            else {
                $data['hand_seals'] = validateHandSeals(
                    system: $system,
                    raw_hand_seals: $_POST['hand_seals'] ?? null,
                    jutsu_id: null,
                    ALL_JUTSU: $ALL_JUTSU
                );
            }

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
        foreach($jutsu_constraints as $var_name => $variable) {
            if(isset($_POST[$var_name])) {
                $data[$var_name] = htmlspecialchars($_POST[$var_name], ENT_QUOTES);
            }
            else {
                $data[$var_name] = '';
            }
        }
    }
    else {
        foreach($jutsu_constraints as $var_name => $variable) {
            $data[$var_name] = '';
        }
    }

    require 'templates/admin/create_jutsu.php';
}

function editJutsuPage(System $system, array $RANK_NAMES) {
    $select_jutsu = true;
    $self_link = $system->router->links['admin'] . '&page=edit_jutsu';

    $ALL_JUTSU = Jutsu::fetchAll($system);

    /* Variables */
    $jutsu_constraints = require 'admin/constraints/jutsu.php';

    // Validate jutsu id
    $jutsu_id = null;
    $jutsu = null;
    if(!empty($_GET['jutsu_id'])) {
        $jutsu_id = (int)$_GET['jutsu_id'];

        $result = $system->query("SELECT * FROM `jutsu` WHERE `jutsu_id`='$jutsu_id'");
        if($system->db_last_num_rows == 0) {
            $system->message("Invalid Jutsu!");
            $system->printMessage();
        }
        else {
            $jutsu_data = $system->db_fetch($result);
            $jutsu = Jutsu::fromArray($jutsu_data['jutsu_id'], $jutsu_data);
            $select_jutsu = false;
        }
    }

    // POST submit edited data
    if(!empty($_POST['jutsu_data']) && !$select_jutsu) {
        try {
            $editing_jutsu_id = $jutsu_id;
            $form_data = $_POST;
            unset($form_data['hand_seals']);

            $data = [];
            validateFormData($jutsu_constraints, $data, $editing_jutsu_id);

            // Do manual hand seal validation
            if($form_data['jutsu_type'] == Jutsu::TYPE_TAIJUTSU) {
                $data['hand_seals'] = "";
            }
            else {
                $data['hand_seals'] = validateHandSeals(
                    system: $system,
                    raw_hand_seals: $_POST['hand_seals'] ?? null,
                    jutsu_id: $jutsu->id,
                    ALL_JUTSU: $ALL_JUTSU
                );
            }

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
            $query .= "WHERE `jutsu_id`='{$jutsu->id}'";

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
    if($jutsu && !$select_jutsu) {
        $existing_jutsu = $jutsu;
        require 'templates/admin/edit_jutsu.php';
    }

    // Show form for selecting ID
    if($select_jutsu) {
        $all_jutsu = Jutsu::fetchAll($system);

        require 'templates/admin/edit_jutsu_select.php';
    }
}


/**
 * @param System     $system
 * @param array|null $raw_hand_seals
 * @param int|null   $jutsu_id
 * @param array      $ALL_JUTSU
 * @return string
 * @throws Exception
 */
function validateHandSeals(System $system, ?array $raw_hand_seals, ?int $jutsu_id, array $ALL_JUTSU) {
    if($raw_hand_seals == null) {
        throw new Exception("Hand seals are required!");
    }

    $hand_seals_arr = array_map('intval', $raw_hand_seals);
    // Remove empty values and rekey the array
    $hand_seals_arr = array_values(
        array_filter(
            $hand_seals_arr,
            function(int $hand_seal) {
                return $hand_seal !== 0;
            },
        )
    );

    if(count($hand_seals_arr) < 1) {
        throw new Exception("Hand seals are required for ninjutsu and genjutsu!");
    }


    $hand_seals_str = implode("-", $hand_seals_arr);
    foreach($ALL_JUTSU as $jutsu) {
        if($jutsu_id != null && $jutsu->id == $jutsu_id) {
            continue;
        }
        if($jutsu->hand_seals === $hand_seals_str) {
            throw new Exception("Hand seals must be unique! ({$jutsu->name} has hand seals {$hand_seals_str})");
        }
    }

    return $hand_seals_str;
}
