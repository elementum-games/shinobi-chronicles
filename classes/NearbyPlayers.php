<?php

class NearbyPlayers {
    // TODO: Refactor this into... something less messy
    public static function renderScoutAreaList(
        System $system,
        User $player,
        string $self_link,
        $in_existing_table = false,
        $show_spar_link = true
    ) {
        // Pagination
        $users_per_page = 30;
        $min = 0;
        if(!empty($_GET['min'])) {
            $min = (int)$system->db->clean($_GET['min']);
        }

        // Load rank data
        $ranks = [];
        $result = $system->db->query("SELECT `rank_id`, `name` FROM `ranks`");
        while($rank = $system->db->fetch($result)) {
            $ranks[$rank['rank_id']]['name'] = $rank['name'];
        }

        $result = $system->db->query(
            "SELECT `user_id`, `user_name`, `rank`, `village`, `exp`, `location`, `battle_id`, `stealth` FROM `users` 
		WHERE `last_active` > UNIX_TIMESTAMP() - 120 ORDER BY `exp` DESC LIMIT $min, $users_per_page"
        );
        $users = [];
        while($row = $system->db->fetch($result)) {
            $location = TravelCoords::fromDbString($row['location']);
            $row['location_string'] = $row['location'];
            $row['location'] = $location;

            $scout_range = $player->scout_range - $row['stealth'];
            if($scout_range < 0) {
                $scout_range = 0;
            }

            if($location->distanceDifference($player->location) <= $scout_range) {
                $users[] = $row;
            }
        }

        // Search box for individual users
        // List top 10 users by experience
        $colspan_attr = '';
        if(!$in_existing_table) {
            echo "<table class='table'>";
        }
        else {
            $colspan_attr = " colspan='5'";
        }

        echo "<tr><th {$colspan_attr}>Scout Area (Scout Range: $player->scout_range squares)</th></tr>";

        if(!$in_existing_table) {
            echo "<tr><td style='text-align:center;'>
        You can view other ninja within your scout range here. You can also attack or issue spar challenges if allowed.
        </td></tr></table>
        <table class='table'>";
        }

        echo "<tr>
            <th style='width:28%;'>Username</th>
            <th style='width:20%;'>Rank</th>
            <th style='width:17%;'>Village</th>
            <th style='width:17%;'>Location</th>
            <th style='width:18%;'>&nbsp;</th>
        </tr>";

        if(is_array($users)) {
            foreach($users as $user) {
                echo "<tr class='table_multicolumns'>
				<td style='width:28%;'><a href='{$system->router->links['members']}&user={$user['user_name']}'>" . $user['user_name'] . "</a></td>
				<td style='width:20%;text-align:center;'>" . $ranks[$user['rank']]['name'] . "</td>
				<td style='width:17%;text-align:center;'>
					<img src='./images/village_icons/" . strtolower($user['village']) . ".png' style='max-height:18px;max-width:18px;' />
				<span style='font-weight:bold;color:" . ($user['village'] == $player->village->name ? '#00C000;' : '#C00000;') .
                    "'>" . $user['village'] . "</span></td>
					
				<td style='width:17%;text-align:center;'>" . $user['location']->displayString() . "</td>
				<td style='width:18%;text-align:center;'>";
                // Attack/spar link
                if($user['battle_id']) {
                    echo "In battle";
                }
                else if($player->location->equals($user['location']) && $user['user_id'] != $player->user_id) {
                    $links = [];

                    // Attack
                    if($show_spar_link) {
                        $links[] = "<a href='{$system->router->links['spar']}}&challenge={$user['user_id']}'>Spar</a>";
                    }

                    echo implode(" | ", $links);
                }
                echo "&nbsp;</td>
			</tr>";
            }

            if(!$in_existing_table) {
                echo "</table>";

                // Pagination
                echo "<p style='text-align:center;'>";
                if($min > 0) {
                    $prev = $min - $users_per_page;
                    if($prev < 0) {
                        $prev = 0;
                    }
                    echo "<a href='$self_link&min=$prev'>Previous</a>";
                }

                if($min + $users_per_page < count($users)) {
                    if($min > 0) {
                        echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
                    }
                    $next = $min + $users_per_page;
                    echo "<a href='$self_link&min=$next'>Next</a>";
                }
                echo "</p>";
            }
        }
    }
}


