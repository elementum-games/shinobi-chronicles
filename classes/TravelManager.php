<?php

class TravelManager {

    const VILLAGE_ICONS = [
        'Stone' => 'images/village_icons/stone.png',
        'Mist' => 'images/village_icons/mist.png',
        'Cloud' => 'images/village_icons/cloud.png',
        'Sand' => 'images/village_icons/sand.png',
        'Leaf' => 'images/village_icons/leaf.png'
    ];
    
    private System $system;
    private User $user;

    public function __construct(System $system, User $user) {
        $this->system = $system;
        $this->user = $user;
    }

    public function getNearbyPlayers(): array {
        $sql = "SELECT `users`.`user_id`, `users`.`user_name`, `users`.`village`, `users`.`rank`, `users`.`stealth`,
                `users`.`level`, `users`.`attack_id`, `users`.`battle_id`, `ranks`.`name`, `users`.`location`
                FROM `users`
                INNER JOIN `ranks`
                ON `users`.`rank`=`ranks`.`rank_id`
                WHERE `users`.`last_active` > UNIX_TIMESTAMP() - 120
                ORDER BY `users`.`exp` DESC";
        $result = $this->system->query($sql);
        $users = $this->system->db_fetch_all($result);
        $return_arr = [];
        foreach ($users as $user) {
            // check if the user is nearby (including stealth
            $scout_range = max(0, $this->user->scout_range - $user['stealth']);
            $location = explode('.', $user['location']);
            if ($location[2] !== $this->user->z ||
                abs($location[0] - $this->user->x) > $scout_range ||
                abs($location[1] - $this->user->y) > $scout_range) {
                continue;
            }
            // village icon
            $user['village_icon'] = $this->system->link . self::VILLAGE_ICONS[$user['village']];

            // if ally or enemy
            // if there were alliance we can do additional checks here
            if ($user['village'] === $this->user->village) {
                $user['alignment'] = 'Ally';
            } else {
                $user['alignment'] = 'Enemy';
            }

            // only display attack links if the same rank
            $user['attackable'] = $user['rank'] === $this->user->rank;

            // add to return
            $return_arr[] = $user;
        }

        return $return_arr;
    }

}