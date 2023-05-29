<?php

function formPreloadData($variables, &$data, $post = true, $post_array = false) {
    if($post_array == false) {
        $post_array = $_POST;
    }
    foreach($variables as $var_name => $variable) {
        if(isset($variable['count']) or is_array(reset($variable))) {
            if(isset($variable['count'])) {
                $data_array = [];
                for($i = 0; $i < $variable['count']; $i++) {
                    $data_array[$i] = [];
                    formPreloadData($variable['variables'], $data_array[$i], $post, $post_array[$var_name][$i]);
                }
                $data[$var_name] = json_encode($data_array);
            }
            else {
            }
        }
        else {
            if(isset($post_array[$var_name]) && $post) {
                $data[$var_name] = htmlspecialchars($post_array[$var_name], ENT_QUOTES);
            }
            else {
                $data[$var_name] = '';
            }
        }
    }
}

/**
 * @param      $entity_constraints
 * @param      $data
 * @param null $content_id
 * @param null $FORM_DATA
 * @throws Exception if any validation error
 */
function validateFormData($entity_constraints, &$data, $content_id = null, $FORM_DATA = null): void {
    if($FORM_DATA == null) {
        $FORM_DATA = $_POST;
    }

    foreach($entity_constraints as $var_name => $variable) {
        if(isset($FORM_DATA[$var_name])) {
            if(isset($variable['count']) or is_array(reset($variable))) {
                // Validate a set number of exact same variables
                if(isset($variable['count'])) {
                    $data_array = [];
                    $count = 0;
                    for($i = 0; $i < $variable['count']; $i++) {
                        $data_array[$count] = [];
                        foreach($variable['variables'] as $name => $var) {
                            if($var['special'] == 'remove' and !empty($FORM_DATA[$var_name][$i][$name])) {
                                $data_array[$count] = [];
                                break;
                            }
                            if(empty($FORM_DATA[$var_name][$i][$name])) {
                                continue;
                            }
                            else {
                                validateField($name, $FORM_DATA[$var_name][$i][$name], $var, $entity_constraints, $data_array[$count], $content_id);
                            }
                        }
                        if(empty($data_array[$count])) {
                            unset($data_array[$count]);
                        }
                        else {
                            $count++;
                        }
                    }
                    if(!isset($variable['num_required'])) {
                        $variable['num_required'] = $variable['count'];
                    }
                    if($count < $variable['num_required']) {
                        throw new Exception("Invalid $var_name! (needs at least " . $variable['num_required'] . ")");
                    }
                    $data[$var_name] = json_encode($data_array);
                }
            }
            else {
                validateField(
                    var_name: $var_name,
                    input: $FORM_DATA[$var_name],
                    FORM_DATA: $FORM_DATA,
                    field_constraints: $variable,
                    all_constraints: $entity_constraints,
                    data: $data,
                    content_id: $content_id
                );
            }
        }
        else {
            throw new Exception("Invalid " . System::unSlug($var_name) . "!");
        }
    }
}

/**
 * @throws Exception
 */
function validateField($var_name, $input, $FORM_DATA, $field_constraints, &$all_constraints, &$data, $content_id = null): bool {
    global $system;
    // Skip variable if it is not required
    if(isset($field_constraints['required_if'])) {
        $req_var = $field_constraints['required_if'];
        // If variable false/not set, continue
        if(empty($data[$req_var]) && empty($FORM_DATA[$req_var])) {
            return true;
        }
        // If variable is set and value matches not required key
        if(!empty($data[$req_var]) && $data[$req_var] == $all_constraints[$req_var]['not_required_value']) {
            return true;
        }
        if(!empty($FORM_DATA[$req_var]) && $FORM_DATA[$req_var] == $all_constraints[$req_var]['not_required_value']) {
            return true;
        }
    }
    // Check for special remove variable
    if(($field_constraints['special'] ?? '') == 'remove') {
        return true;
    }

    $data[$var_name] = $system->clean($input);

    // Check for entry
    if(strlen($data[$var_name]) < 1) {
        throw new Exception("Please enter " . System::unSlug($var_name) . "!");
    }
    // Check numeric variables
    if($field_constraints['data_type'] != 'string') {
        if(!is_numeric($data[$var_name])) {
            throw new Exception("Invalid " . System::unSlug($var_name) . "!");
        }
    }
    // Check variable matches restricted possibles list, if any
    if(!empty($field_constraints['options'])) {
        if($field_constraints['data_type'] == 'string') {
            if(array_search($data[$var_name], $field_constraints['options']) === false && $var_name != 'elements') {
                throw new Exception("Invalid " . System::unSlug($var_name) . "!");
            }
        }
        else {
            if(!isset($field_constraints['options'][$data[$var_name]])) {
                throw new Exception("Invalid " . System::unSlug($var_name) . "!");
            }
        }
    }
    // Check number min/max
    if(in_array($field_constraints['data_type'], ['int', 'float'])) {
        if(isset($field_constraints['min']) && $data[$var_name] < $field_constraints['min']) {
            throw new Exception(System::unSlug($var_name) . " cannot be lower than {$field_constraints['min']}!");
        }
        if(isset($field_constraints['max']) && $data[$var_name] > $field_constraints['max']) {
            throw new Exception(System::unSlug($var_name) . " cannot be higher than {$field_constraints['max']}!");
        }
    }

    // Check max length
    if(isset($field_constraints['max_length'])) {
        if(strlen($data[$var_name]) > $field_constraints['max_length']) {
            throw new Exception(System::unSlug($var_name) .
                " is too long! (" . strlen($data[$var_name]) . "/" . $field_constraints['max_length'] . " chars)"
            );
        }
    }
    // Check pattern
    if(isset($field_constraints['pattern'])) {
        if(!preg_match($field_constraints['pattern'], $data[$var_name])) {
            throw new Exception("Invalid " . System::unSlug($var_name) . " ({$data[$var_name]})!");
        }
    }

    // Check for uniqueness
    if(isset($field_constraints['unique_required']) && $field_constraints['unique_required'] == true) {
        if($content_id) {
            $query = "SELECT `{$field_constraints['unique_column']}` FROM `{$field_constraints['unique_table']}` 
				WHERE `{$field_constraints['unique_column']}` = '" . $data[$var_name] . "' and `{$field_constraints['id_column']}` != '$content_id' LIMIT 1";
        }
        else {
            $query = "SELECT `{$field_constraints['unique_column']}` FROM `{$field_constraints['unique_table']}` 
				WHERE `{$field_constraints['unique_column']}` = '" . $data[$var_name] . "' LIMIT 1";
        }
        $result = $system->query($query);
        if($system->db_last_num_rows > 0) {
            throw new Exception("'" . System::unSlug($var_name) . "' needs to be unique, the value '" . $data[$var_name] . "' is already taken!");
        }
    }

    return true;
}

function displayFormFields($variables, $data, $input_name_prefix = ''): bool {
    echo "<style>
        label {
            display:inline-block;
            width:120px;
        }
    </style>";

    foreach($variables as $var_name => $variable) {
        // Variable is an array of sub-variables
        if(isset($variable['count']) or is_array(reset($variable))) {
            // Display a set number of exact same variables
            if(isset($variable['count'])) {
                echo "<label for='{$var_name}'>" . System::unSlug($var_name) . ":</label>" .
                    (isset($variable['num_required']) ? "<i>(" . $variable['num_required'] . " required)</i>" : "") .
                    "<div style='margin-left:20px;margin-top:0;'>";
                $data_vars = json_decode($data[$var_name], true);
                for($i = 0; $i < $variable['count']; $i++) {
                    $name = $var_name . '[' . $i . ']';
                    echo "<span style='display:block;margin-top:10px;font-weight:bold;'>#" . ($i + 1) .
                        ": <button onclick='$(\"#" . $var_name . '_' . $i . "\").toggle();return false;'>Show/Hide</button></span>";

                    $sub_form_style = count($variable['variables']) > 4 ?
                        "display:none;margin-left:26px;" :
                        "margin-left:26px;";
                    echo "<div id='" . $var_name . '_' . $i . "' style='{$sub_form_style}'>";
                    displayFormFields($variable['variables'], $data_vars[$i], $name);
                    echo "</div>";
                }
                if(!empty($variable['deselect'])) {
                    $name = $var_name;
                    if($input_name_prefix) {
                        $name = $input_name_prefix . '[' . $name . ']';
                    }
                    echo "<br />
					<input type='radio' name='name' value='none' />None<br />";
                }
                echo "</div>";
            }
            // Display unique data structure based on array key names
            else {
                echo "<label for='$var_name'>" .
                    System::unSlug($var_name) .
                    ":</label>
				<p style='margin-left:20px;margin-top:0;'>";
                $data_vars = json_decode($data[$var_name], true);
                displayFormFields($variable, $data_vars, $var_name);
                if(!empty($variable['deselect'])) {
                    $name = $var_name;
                    if($input_name_prefix) {
                        $name = $input_name_prefix . '[' . $name . ']';
                    }
                    echo "<br />
					<input type='radio' name='name' value='none' />None<br />";
                }
                echo "</p>";
            }
        }
        else {
            displayVariable($var_name, $variable, $data[$var_name], $input_name_prefix);
        }
    }
    return true;
}

function displayVariable($var_name, $variable, $current_value, $input_name_prefix = ''): bool {
    // Set input name
    $name = $var_name;
    if($input_name_prefix) {
        $name = $input_name_prefix . '[' . $name . ']';
    }

    $input_type = $variable['input_type'] ?? '';

    if($input_type == 'text') {
        echo "<label for='$name'>" . System::unSlug($var_name) . ":</label>
		<input type='text' name='$name' value='" . stripslashes($current_value) . "' /><br />";
    }
    else if($variable['input_type'] == 'text_area') {
        echo "<label for='$var_name'>" . System::unSlug($var_name) . ":</label><br />
            <textarea name='$var_name' rows='3' style='width:60%;max-width:400px;'>"
            . stripslashes($current_value)
            . "</textarea><br />";
    }
    else if($input_type == 'radio' && !empty($variable['options'])) {
        echo "<label for='$name' style='margin-top:5px;'>" . System::unSlug($var_name) . ":</label>
		<p style='padding-left:10px;margin-top:5px;'>";
        $count = 1;
        foreach($variable['options'] as $id => $option) {
            if($variable['data_type'] == 'int' || $variable['data_type'] == 'float') {
                echo "<input type='radio' name='$name' value='$id' " .
                    ($current_value == $id ? "checked='checked'" : '') .
                    " />" . ucwords(str_replace("_", " ", $option));
                $count++;
            }
            else if($variable['data_type'] == 'string') {
                echo "<input type='radio' name='$name' value='$option' " .
                    ($current_value == $option ? "checked='checked'" : '') .
                    " />" . ucwords(str_replace("_", " ", $option));
            }
            echo "<br />";
        }
        echo "</p>";
    }
    else if($input_type == 'select' && !empty($variable['options'])) {
        echo "<label for='$name' style='margin-top:5px;'>" . System::unSlug($var_name) . ":</label>
		<select name='{$name}'>
		";
        $count = 1;
        foreach($variable['options'] as $id => $option) {
            if($variable['data_type'] == 'int' || $variable['data_type'] == 'float') {
                echo "<option name='$name' value='$id' " .
                    ($current_value == $id ? "selected='selected'" : '') .
                    ">" . ucwords(str_replace("_", " ", $option)) . "</option>";
                $count++;
            }
            else if($variable['data_type'] == 'string') {
                echo "<option name='$name' value='$option' " .
                    ($current_value == $option ? "selected='selected'" : '') .
                    ">" . ucwords(str_replace("_", " ", $option)) . "</option>";
            }
        }
        echo "</select><br />";
    }
    else if($input_type == 'checkbox' && isset($variable['options'])) {
        if(json_decode($current_value)) {
            $current_value = json_decode($current_value, true);
        }
        echo "<label for='$name' style='margin-top:5px;'>" . System::unSlug($var_name) . ":</label><br />";
        foreach($variable['options'] as $value) {
            echo "<input style='margin-left:15px;' type='checkbox' name='{$name}[]' value='$value'";
                if(is_array($current_value)) {
                    if($name == 'elements') {
                        if(isset($current_value['first']) && $current_value['first'] == $value) {
                            echo "checked='checked' ";
                        }
                        if(isset($current_value['second']) && $current_value['second'] == $value) {
                            echo "checked='checked' ";
                        }
                    }
                    else if(in_array($value, $current_value)) {
                        echo "checked='checked' ";
                    }
                }
                else {
                    if($current_value == $value) {
                        echo "checked='checked' ";
                    }
                }
            echo "/>$value<br />";
        }
        if($name == 'elements') {
            echo "<b>Important:</b> Changing only a single element will retain the first/second nature selections.<br />
            Changing both will result in the 'first' element being whichever appears first on this list.<br />";
        }
    }
    else if(!empty($variable['special']) && $variable['special'] == 'remove') {
        echo "<label for='$name' style='margin-top:5px;'>Remove:</label>
		<p style='padding-left:10px;margin-top:5px;'>
			<input type='checkbox' name='$name' value='1' />";
    }
    else {
        echo "Coming soon!<br />";
    }
    return true;
}