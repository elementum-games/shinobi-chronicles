<?php

function forbiddenShop(): void {
	global $system;
	global $player;

    $result = $system->db->query(
        "SELECT * FROM `maps_locations` WHERE `map_id`={$player->location->map_id}"
    );

    $locations = $system->db->fetch_all($result);

    $index = null;
    foreach ($locations as $key => $location) {
        if (isset($location['name']) && $location['name'] === "Ayakashi's Abyss") {
            $index = $key;
            break;
        }
    }

    if ($index !== null) {
        if ($player->location->x == $locations[$index]['x'] && $player->location->y == $locations[$index]['y']) {
            $system->event != null ? throw new RuntimeException("The abyss is silent...") : require 'templates/forbiddenShop.php';
        } else {
            throw new RuntimeException("Invalid location!");
        }
    } else {
        throw new RuntimeException("Location not found!!");
    }
}