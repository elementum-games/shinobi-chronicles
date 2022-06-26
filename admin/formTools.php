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
 * @param      $variables
 * @param      $data
 * @param null $content_id
 * @throws Exception if any validation error
 */
function validateFormData($variables, &$data, $content_id = null) {
    foreach($variables as $var_name => $variable) {
        if(isset($_POST[$var_name])) {
            if(isset($variable['count']) or is_array(reset($variable))) {
                // Validate a set number of exact same variables
                if(isset($variable['count'])) {
                    $data_array = [];
                    $count = 0;
                    for($i = 0; $i < $variable['count']; $i++) {
                        $data_array[$count] = [];
                        foreach($variable['variables'] as $name => $var) {
                            if($var['special'] == 'remove' and !empty($_POST[$var_name][$i][$name])) {
                                $data_array[$count] = [];
                                break;
                            }
                            if(empty($_POST[$var_name][$i][$name])) {
                                continue;
                            }
                            else {
                                validateVariable($name, $_POST[$var_name][$i][$name], $var, $variables, $data_array[$count], $content_id);
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
                else {
                }
            }
            else {
                validateVariable($var_name, $_POST[$var_name], $variable, $variables, $data, $content_id);
            }
        }
        else {
            throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
        }
    }
}

function validateVariable($var_name, $input, $variable, &$variables, &$data, $content_id = null): bool {
    global $system;
    // Skip variable if it is not required
    if(isset($variable['required_if'])) {
        $req_var = $variable['required_if'];
        // If variable false/not set, continue
        if(empty($data[$req_var]) && empty($_POST[$req_var])) {
            return true;
        }
        // If variable is set and value matches not required key
        if(!empty($data[$req_var]) && $data[$req_var] == $variables[$req_var]['not_required_value']) {
            return true;
        }
        if(!empty($_POST[$req_var]) && $_POST[$req_var] == $variables[$req_var]['not_required_value']) {
            return true;
        }
    }
    // Check for special remove variable
    if(($variable['special'] ?? '') == 'remove') {
        return true;
    }
    $data[$var_name] = $system->clean($input);
    // Check for entry
    if(strlen($data[$var_name]) < 1) {
        throw new Exception("Please enter " . ucwords(str_replace("_", " ", $var_name)) . "!");
    }
    // Check numeric variables
    if($variable['data_type'] != 'string') {
        if(!is_numeric($data[$var_name])) {
            throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
        }
    }
    // Check variable matches restricted possibles list, if any
    if(!empty($variable['options'])) {
        if($variable['data_type'] == 'string') {
            if(array_search($data[$var_name], $variable['options']) === false) {
                throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
            }
        }
        else {
            if(!isset($variable['options'][$data[$var_name]])) {
                throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
            }
        }
    }
    // Check max length
    if(isset($variable['max_length'])) {
        if(strlen($data[$var_name]) > $variable['max_length']) {
            throw new Exception(ucwords(str_replace("_", " ", $var_name)) .
                " is too long! (" . strlen($data[$var_name]) . "/" . $variable['max_length'] . " chars)"
            );
        }
    }
    // Check pattern
    if(isset($variable['pattern'])) {
        if(!preg_match($variable['pattern'], $data[$var_name])) {
            throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
        }
    }

    // Check for uniqueness
    if(isset($variable['unique_required']) && $variable['unique_required'] == true) {
        if($content_id) {
            $query = "SELECT `{$variable['unique_column']}` FROM `{$variable['unique_table']}` 
				WHERE `{$variable['unique_column']}` = '" . $data[$var_name] . "' and `{$variable['id_column']}` != '$content_id' LIMIT 1";
        }
        else {
            $query = "SELECT `{$variable['unique_column']}` FROM `{$variable['unique_table']}` 
				WHERE `{$variable['unique_column']}` = '" . $data[$var_name] . "' LIMIT 1";
        }
        $result = $system->query($query);
        if($system->db_last_num_rows > 0) {
            throw new Exception("'" . ucwords(str_replace("_", " ", $var_name)) . "' needs to be unique, the value '" . $data[$var_name] . "' is already taken!");
        }
    }

    return true;
}

function displayFormFields($variables, $data, $input_name_prefix = ''): bool {
    foreach($variables as $var_name => $variable) {
        // Variable is an array of sub-variables
        if(isset($variable['count']) or is_array(reset($variable))) {
            // Display a set number of exact same variables
            if(isset($variable['count'])) {
                echo "<label for='{$var_name}'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>" .
                    (isset($variable['num_required']) ? "<i>(" . $variable['num_required'] . " required)</i>" : "") .
                    "<div style='margin-left:20px;margin-top:0px;'>";
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
                    ucwords(str_replace("_", " ", $var_name)) .
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
        echo "<label for='$name'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
		<input type='text' name='$name' value='" . stripslashes($current_value) . "' /><br />";
    }
    else if($input_type == 'radio' && !empty($variable['options'])) {
        echo "<label for='$name' style='margin-top:5px;'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
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
        echo "<label for='$name' style='margin-top:5px;'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
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
    else if($variable['special'] == 'remove') {
        echo "<label for='$name' style='margin-top:5px;'>Remove:</label>
		<p style='padding-left:10px;margin-top:5px;'>
			<input type='checkbox' name='$name' value='1' />";
    }
    else {
        echo "Coming soon!<br />";
    }
    return true;
}