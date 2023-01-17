<?php

function jutsu(): void {
    global $system;

    global $player;

    global $self_link;

    $player->getInventory();

    $max_equipped_jutsu = 3;
    if($player->rank >= 3) {
        $max_equipped_jutsu++;
    }
    if($player->rank >= 4) {
        $max_equipped_jutsu++;
    }
    if($player->forbidden_seal && $player->forbidden_seal['level'] >= 2) {
        $max_equipped_jutsu++;
    }

    if(!empty($_POST['equip_jutsu'])) {
        $jutsu = $_POST['jutsu'];
        $equipped_jutsu = array();

        try {
            $count = 0;
            $jutsu_types = array('ninjutsu', 'taijutsu', 'genjutsu');
            foreach($jutsu as $jutsu_data) {
                if($count >= $max_equipped_jutsu) {
                    break;
                }

                $jutsu_array = explode('-', $jutsu_data);
                if($jutsu_array[0] == 'none') {
                    continue;
                }

                if(!in_array($jutsu_array[0], $jutsu_types)) {
                    throw new Exception("Invalid jutsu type!");
                }
                if($player->checkInventory($jutsu_array[1], 'jutsu')) {
                    $equipped_jutsu[$count]['id'] = $system->clean($jutsu_array[1]);
                    $equipped_jutsu[$count]['type'] = $system->clean($jutsu_array[0]);
                    $count++;
                }
            }

            $player->equipped_jutsu = $equipped_jutsu;
            $system->message("Jutsu equipped!");
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    if(!empty($_GET['learn_jutsu'])) {
        $jutsu_id = (int)$_GET['learn_jutsu'];
        try {
            if(!isset($player->jutsu_scrolls[$jutsu_id])) {
                throw new Exception("Invalid jutsu!");
            }
            if($player->checkInventory($jutsu_id, 'jutsu')) {
                throw new Exception("You already know that jutsu!");
            }

            // Parent jutsu check
            if($player->jutsu_scrolls[$jutsu_id]->parent_jutsu) {
                $id = $player->jutsu_scrolls[$jutsu_id]->parent_jutsu;
                if(!isset($player->jutsu[$id])) {
                    throw new Exception("You need to learn " . $player->jutsu[$id]->name . " first!");
                }

                if($player->jutsu[$id]->level < 50) {
                    throw new Exception("You are not skilled enough with " . $player->jutsu[$id]->name .
                        "! (Level " . $player->jutsu[$id]->level . "/50)");
                }
            }

            $player->jutsu[$jutsu_id] = $player->jutsu_scrolls[$jutsu_id];
            $player->jutsu[$jutsu_id]->setLevel(1, 0);
            $jutsu_name = $player->jutsu_scrolls[$jutsu_id]->name;

            switch($player->jutsu[$jutsu_id]->jutsu_type) {
                case 'ninjutsu':
                    $player->ninjutsu_ids[] = $jutsu_id;
                    break;
                case 'taijutsu':
                    $player->taijutsu_ids[] = $jutsu_id;
                    break;
                case 'genjutsu':
                    $player->genjutsu_ids[] = $jutsu_id;
                    break;
            }

            unset($player->jutsu_scrolls[$jutsu_id]);
            $system->message("You have learned $jutsu_name!");
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    else if(!empty($_GET['forget_jutsu'])) {
        $jutsu_id = (int)$_GET['forget_jutsu'];
        try{
            //Checking if player knows the jutsu he's trying to forget.
            if(!$player->checkInventory($jutsu_id, 'jutsu')) {
                throw new Exception("Invalid Jutsu!");
            }

            //Checking if player has jutsu that depend on the jutsu he's trying to forget.
            $can_forget = userHasChildrenJutsu($jutsu_id, $player);
            if(!$can_forget){
                throw new Exception("You cannot forget the parent of a jutsu you know!");
            }

            if(!empty($_POST['confirm_forget'])) {
                //Forgetting jutsu.
                $jutsu_name = $player->jutsu[$jutsu_id]->name;

                //refund input verification
                $refund = ($player->jutsu[$jutsu_id]->purchase_cost * 0.1); //10% Refund
                $refund = intval(round($refund)); //round and then convert Float=>Int
                if($refund > 0 && gettype($refund) == "integer"){
                    $player->addMoney($refund, "Sell jutsu");
                }

                $player->removeJutsu($jutsu_id);

                //css: Overlap caused by css Position property
                $system->message("You have forgotten $jutsu_name!<br>You were refunded ¥{$refund}");
                $system->printMessage();
            }
            else {
                echo "<table class='table'>
					    <tr>
					        <th>Forget Jutsu</th>
                        </tr>
					    <tr>
					        <td style='text-align:center;'>
						        Are you sure you want to forget {$player->jutsu[$jutsu_id]->name}?
						        <br />
                                <form action='$self_link&forget_jutsu={$jutsu_id}' method='post'>
                                    <input type='hidden' name='confirm_forget' value='1' />
                                    <button style='text-align:center' type='submit'>Forget</button>
                                </form>
					        </td>
					    </tr>
				    </table>";
            }


        }
        catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    $system->printMessage();
    echo "<table class='table'>";

    // View single jutsu details
    $jutsu_list = true;

    if(!empty($_GET['view_jutsu'])) {
        $jutsu_list = false;
        $jutsu_id = (int)$system->clean($_GET['view_jutsu']);
        if(!isset($player->jutsu[$jutsu_id])) {
            $system->message("Invalid jutsu!");
            $system->printMessage();
        }
        else {
            $jutsu = $player->jutsu[$jutsu_id];
            echo "<tr><th>" . $jutsu->name . " (<a href='$self_link'>Return</a>)</th></tr>
			<tr><td>
				<label style='width:6.5em;'>Rank:</label>" . $jutsu->rank . "<br />";
            if($jutsu->element != 'none') {
                echo "<label style='width:6.5em;'>Element:</label>" . $jutsu->element . "<br />";
            }
            echo "<label style='width:6.5em;'>Use cost:</label>" . $jutsu->use_cost . "<br />";
            if($jutsu->jutsu_type != 'taijutsu') {
                echo "<label style='width:6.5em;'>Hand seals:</label>" . $jutsu->hand_seals . "<br />";
            }
            if($jutsu->cooldown) {
                echo "<label style='width:6.5em;'>Cooldown:</label>" . $jutsu->cooldown . " turn(s)<br />";
            }
            if($jutsu->effect) {
                echo "<label style='width:6.5em;'>Effect:</label>" .
                    ucwords(str_replace('_', ' ', $jutsu->effect)) . " - " . $jutsu->effect_length . " turns<br />";
            }
            echo "<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu->jutsu_type) . "<br />
				<label style='width:6.5em;'>Power:</label>" . round($jutsu->power, 1) . "<br />
				<label style='width:6.5em;'>Level:</label>" . $jutsu->level . "<br />
				<label style='width:6.5em;'>Exp:</label>" . $jutsu->exp . "<br />";

            echo "<label style='width:6.5em;float:left;'>Description:</label>
					<p style='display:inline-block;margin:0;width:37.1em;'>" . $jutsu->description . "</p>
					<br style='clear:both;' />";

            $result = $system->query("SELECT `name` FROM `jutsu` WHERE `parent_jutsu`='$jutsu_id'");
            if($system->db_last_num_rows > 0) {
                echo "<br />
					<br /><label>Learn <b>" . $jutsu->name . "</b> to level 50 to unlock:</label>
						<p style='margin-left:10px;margin-top:5px;'>";
                while($row = $system->db_fetch($result)) {
                    echo $row['name'] . "<br />";
                }
                echo "</p>";
            }

            echo "<p style='text-align:center'><a href='$self_link&view_jutsu={$jutsu->id}&forget_jutsu={$jutsu->id}'>Forget Jutsu!</a></p>";
            echo "</td></tr>";
        }
    }

    if($jutsu_list) {
        echo "<tr>
			<th id='ninjutsu_title_header' style='width:33%;'>Ninjutsu</th>
			<th id='taijutsu_title_header' style='width:33%;'>Taijutsu</th>
			<th id='genjutsu_title_header' style='width:33%;'>Genjutsu</th>
		</tr>";

        echo "<tr><td id='ninjutsu_table_data'>";
        if($player->ninjutsu_ids) {
            $sortedJutsu = array();
            foreach($player->ninjutsu_ids as $jutsu_id) {
                $sortedJutsu[] = $player->jutsu[$jutsu_id]->rank;
            }
            array_multisort($sortedJutsu, $player->ninjutsu_ids);
            foreach ($player->ninjutsu_ids as $jutsu_id) {
                echo "<a href='$self_link&view_jutsu=$jutsu_id' title='Level: {$player->jutsu[$jutsu_id]->level}'>" . $player->jutsu[$jutsu_id]->name . "</a><br />";
            }
        }
        echo "</td>";

        echo "<td id='taijutsu_table_data'>";
        if($player->taijutsu_ids) {
            $sortedJutsu = array();
            foreach($player->taijutsu_ids as $jutsu_id) {
                $sortedJutsu[] = $player->jutsu[$jutsu_id]->rank;
            }
            array_multisort($sortedJutsu, $player->taijutsu_ids);
            foreach($player->taijutsu_ids as $jutsu_id) {
                echo "<a href='$self_link&view_jutsu=$jutsu_id' title='Level: {$player->jutsu[$jutsu_id]->level}'>" . $player->jutsu[$jutsu_id]->name . "</a><br />";
            }
        }
        echo "</td>";

        echo "<td id='genjutsu_table_data'>";
        if($player->genjutsu_ids) {
            $sortedJutsu = array();
            foreach($player->genjutsu_ids as $jutsu_id) {
                $sortedJutsu[] = $player->jutsu[$jutsu_id]->rank;
            }
            array_multisort($sortedJutsu, $player->genjutsu_ids);
            foreach($player->genjutsu_ids as $jutsu_id) {
                echo "<a href='$self_link&view_jutsu=$jutsu_id' title='Level: {$player->jutsu[$jutsu_id]->level}'>" . $player->jutsu[$jutsu_id]->name . "</a><br />";
            }
        }
        echo "</td></tr>";
        echo "<tr><th colspan='3'>Equipped Jutsu</th></tr>";

        echo "<tr><td colspan='3'>
		<form action='$self_link' method='post'>
		<div style='text-align:center;'>";

        echo "<div style='display:inline-block;'>";
        $row_start = 1;
        for($i = 0; $i < $max_equipped_jutsu; $i++) {
            $slot_equipped_jutsu = $player->equipped_jutsu[$i]['id'] ?? null;
            echo "<select name='jutsu[" . ($i + 1) . "]'>
			<option value='none' " . (!$player->equipped_jutsu ? "selected='selected'" : "") . ">None</option>";
            foreach($player->jutsu as $jutsu) {
                echo "<option value='{$jutsu->jutsu_type}-{$jutsu->id}' " .
                    ($jutsu->id == $slot_equipped_jutsu ? "selected='selected'" : "") .
                    ">{$jutsu->name}</option>";
            }
            echo "</select><br />";

            // Start second row
            if($row_start++ > 2) {
                echo "</div><div style='display:inline-block;'>";
                $row_start = 1;
            }
        }
        echo "</div><br />";

        echo "<input type='submit' name='equip_jutsu' value='Equip' />
		</div>
		</form>
		</tr>";


        // Purchase jutsu
        if(!empty($player->jutsu_scrolls)) {
            echo "<tr><th colspan='3'>Jutsu scrolls</th></tr>";

            foreach($player->jutsu_scrolls as $id => $jutsu_scroll) {
                echo "<tr id='jutsu_scrolls' ><td colspan='3'>
					<span style='font-weight:bold;'>" . $jutsu_scroll->name . "</span><br />
					<div style='margin-left:2em;'>
						<label style='width:6.5em;'>Rank:</label>" . $jutsu_scroll->rank . "<br />
						<label style='width:6.5em;'>Element:</label>" . $jutsu_scroll->element . "<br />
						<label style='width:6.5em;'>Use cost:</label>" . $jutsu_scroll->use_cost . "<br />" .
                    ($jutsu_scroll->cooldown ? "<label style='width:6.5em;'>Cooldown:</label>" . $jutsu_scroll->cooldown . " turn(s)<br />" : "") .
                    "<label style='width:6.5em;float:left;'>Description:</label>
						<p style='display:inline-block;margin:0;width:37.1em;'>" . $jutsu_scroll->description . "</p>
						<br style='clear:both;' />
						<label style='width:6.5em;'>Jutsu type:</label>" . ucwords($jutsu_scroll->jutsu_type) . "<br />
					</div>
					<p style='text-align:right;margin:0;'><a href='$self_link&learn_jutsu=$id'>Learn</a></p>
				</td></tr>";
            }
        }
    }

    echo "</table>";

    $player->updateInventory();
}

function userHasChildrenJutsu($id, $player): bool {
    foreach($player->jutsu as $element){
        if($id == $element->parent_jutsu){
            return false;
        }
    }

    return true;
}
