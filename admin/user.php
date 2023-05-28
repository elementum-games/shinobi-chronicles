<?php /** @noinspection SqlWithoutWhere */

/**
 * @throws Exception
 */
function activateUserPage(System $system, User $player): void {
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
			<form action='{$system->router->getUrl('admin', ['page' => 'activate_user'])}' method='post'>
			<b>-Username-</b><br />
			<input type='text' name='activate' /><br />
			<input type='submit' value='Activate' />
			</form>
		</td></tr></table>";
}

/**
 * @throws Exception
 */
function editUserPage(System $system, User $player): void {
    $select_user = true;

    $constraints = require 'admin/entity_constraints.php';
    $variables =& $constraints['edit_user'];

    if($player->isHeadAdmin()) {
        $variables ['elements'] = [
            'data_type' => 'string',
            'input_type' => 'checkbox',
            'options' => User::$ELEMENTS,
        ];
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
    if(isset($_GET['user_name'])) {
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
    if(isset($_POST['user_data']) && !$select_user) {
        try {
            // Additional data checking for Elements
            $new_elements = array();
            $current_elements = json_decode($user_data['elements'], true);
            if($user_data['rank'] >= 3) {
                $required_elements = ($_POST['rank'] == 4) ? 2 : 1;
                if(count($_POST['elements']) > $required_elements) {
                    throw new Exception("Only $required_elements element(s) allowed!");
                }
                if(count($_POST['elements']) < $required_elements) {
                    throw new Exception("There must be at least $required_elements element(s)!");
                }
                if(!is_array($_POST['elements'])) {
                    throw new Exception("Elements form data must be of type array!");
                }
                foreach($_POST['elements'] as $num => $element) {
                    $key = ($num == 0) ? 'first' : 'second';
                    if(!in_array($element, User::$ELEMENTS)) {
                        throw new Exception("Invalid element ($element).");
                    }
                    $new_elements[$key] = $element;
                }

                // Prevent overwriting primary element based on admin panel structure
                if($new_elements['second'] == $current_elements['first'] || $new_elements['first'] == $current_elements['second']) {
                    $new_elements = [
                        'first' => $new_elements['second'],
                        'second' => $new_elements['first']
                    ];
                }

                //Assign new data as json string
                $_POST['elements'] = json_encode($new_elements);
            }
            else {
                //Remove elements in case rank has been reduced
                if($player->isHeadAdmin()) {
                    unset($_POST['elements']);
                    unset($variables['elements']);
                }
            }
            // Load form data
            $data = [];
            validateFormData($variables, $data);

            // Insert into database
            $column_names = '';
            $column_data = '';
            $count = 1;
            $query = "UPDATE `users` SET";
            foreach($data as $name => $var) {
                //Allow quotes for elements to store in db
                if($name == 'elements') {
                    $var = htmlspecialchars_decode($var, ENT_QUOTES);
                }
                $query .= "`$name` = '$var'";
                if($count < count($data)) {
                    $query .= ', ';
                }
                $count++;
            }
            $query .= "WHERE `user_id`='{$user_data['user_id']}'";
            //echo $query;
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
    if(!empty($user_data) && !$select_user) {
        $data =& $user_data;
        echo "<table class='table'><tr><th>Edit User (" . stripslashes($data['user_name']) . ")</th></tr>
			<tr><td>
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_user', 'user_name' => $data['user_name']])}' method='post'>";
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
			<form action='{$system->router->getUrl('admin', ['page' => 'edit_user'])}' method='get'>
			<b>Username</b><br />
			<input type='hidden' name='page' value='edit_user' />
			<input type='hidden' name='id' value='{$_GET['id']}'' />
			<input type='text' name='user_name' /><br />
			<input type='submit' value='Edit' />
			</form>
			</td></tr></table>";
    }
}

/**
 * @throws Exception
 */
function statCutPage(System $system, User $player): void {
    $self_link = $system->router->getUrl('admin', ['page' => 'stat_cut']);
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

            $query = "UPDATE `users` SET";
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

/**
 * @throws Exception
 */
function deleteUserPage(System $system, User $player): void {
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
						<form action='{$system->router->links['admin']}&page=delete_user' method='post'>
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
				<form action='{$system->router->getUrl('admin', ['page' => 'delete_user'])}' method='post'>
				<b>Username</b><br />
				<input type='text' name='user_name' /><br />
				<input type='submit' name='Delete' />
				</form>
			</td></tr></table>";
    }
}

function devToolsPage(System $system, User $player): void {
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
            if($user->user_id == $player->user_id) {
                $player->exp = $total_stats * 10;
            }

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

/**
 * @throws Exception
 */
function giveBloodlinePage(System $system): void {
    // Fetch BL list
    $result = $system->query("SELECT `bloodline_id`, `name` FROM `bloodlines`");
    if($system->db_last_num_rows == 0) {
        $system->message("No bloodlines in database!");
        $system->printMessage();
        return;
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
		<form action='{$system->router->getUrl('admin', ['page' => 'give_bloodline'])}' method='post'>
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