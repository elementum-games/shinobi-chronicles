<?php

require_once __DIR__ . "/battle/Battle.php";
require_once __DIR__ . "/Route.php";

// NOTES: Routes are initialized at the bottom of this file.
class Router {
    // Keep in sync with pages
    const PAGE_IDS = [
        'profile' => 1,
        'inbox' => 2,
        'settings' => 3,
        'members' => 6,
        'villageHQ' => 9,
        'bloodline' => 10,
        'travel' => 11,
        'arena' => 12,
        'mission' => 14,
        'specialmissions' => 15,
        'mod' => 16,
        'admin' => 17,
        'report' => 18,
        'battle' => 19,
        'premium' => 21,
        'spar' => 22,
        'team' => 24,
        'rankup' => 25,
        'event' => 27,
        'marriage' => 29,
        'support' => 30,
        'chat_log' => 31,
        'news' => 32,
        'battle_history' => 33,
    ];

    /** @var Route[] $routes */
    public static array $routes;

    public string $base_url;

    public array $links = [
        'github' => 'https://github.com/elementum-games/shinobi-chronicles',
        'discord' => 'https://discord.gg/Kx52dbXEf3',
    ];
    public array $api_links = [
        'battle' => ''
    ];

    public function __construct(string $base_url) {
        $this->base_url = $base_url;

        foreach(self::PAGE_IDS as $slug => $id) {
            $this->links[$slug] = $this->base_url . '?id=' . $id;
        }

        $this->api_links['battle'] = $this->base_url . 'api/battle.php';
        $this->api_links['inbox'] = $this->base_url . 'api/inbox.php';
        $this->api_links['travel'] = $this->base_url . 'api/travel.php';
    }

    /**
     * @param string $page_name
     * @return string
     * @throws Exception
     */
    public function getUrl(string $page_name, array $url_params = []): string {
        $id = self::PAGE_IDS[$page_name] ?? null;
        if($id == null) {
            throw new Exception("Invalid page name!");
        }

        $extra_params_str = "";
        foreach($url_params as $key => $val) {
            $extra_params_str .= "&{$key}={$val}";
        }

        return $this->base_url . '?id=' . $id . $extra_params_str;
    }

    /**
     * @param Route $route
     * @param User  $player
     * @return void
     * @throws Exception
     */
    public function assertRouteIsValid(Route $route, User $player): void {
        $system = $player->system;
        $routes = self::$routes;


        // Check for battle if page is restricted
        if(isset($route->battle_ok) && $route->battle_ok === false) {
            if($player->battle_id) {
                $contents_arr = [];
                foreach($_GET as $key => $val) {
                    $contents_arr[] = "GET[{$key}]=$val";
                }
                foreach($_POST as $key => $val) {
                    $contents_arr[] = "POST[{$key}]=$val";
                }
                $player->log(User::LOG_IN_BATTLE, implode(',', $contents_arr));
                throw new Exception("You cannot visit this page while in battle!");
            }
        }

        //Check for survival mission restricted
        if(isset($route->survival_ok) && $route->survival_ok === false) {
            if(isset($_SESSION['ai_defeated']) && $player->mission_stage['action_type'] == 'combat') {
                throw new Exception("You cannot move while under attack!");
            }
        }

        // Check for spar/fight PvP type, stop page if trying to load spar/battle while in NPC battle
        if(isset($route->battle_type)) {
            $result = $system->query("SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1");
            if($system->db_last_num_rows > 0) {
                $battle_type = $system->db_fetch($result)['battle_type'];
                if($battle_type != $route->battle_type) {
                    throw new Exception("You cannot visit this page while in combat!");
                }
            }
        }

        if(isset($route->user_check)) {
            if(!($route->user_check instanceof Closure)) {
                throw new Exception("Invalid user check!");
            }

            $page_ok = $route->user_check->call($this, $player);

            if(!$page_ok) {
                throw new Exception("");
            }
        }

        // Check for being in village is not okay/okay/required
        if(isset($route->village_ok)) {
            // Player is allowed in up to rank 3, then must go outside village
            if($player->rank_num > 2 && $route->village_ok === Route::NOT_IN_VILLAGE
                && TravelManager::locationIsInVillage($system, $player->location)
            ) {
                throw new Exception("You cannot access this page while in a village!");
            }

            if($route->village_ok === Route::ONLY_IN_VILLAGE && !$player->location->equals($player->village_location)) {
                $contents_arr = [];
                foreach($_GET as $key => $val) {
                    $contents_arr[] = "GET[{$key}]=$val";
                }
                foreach($_POST as $key => $val) {
                    $contents_arr[] = "POST[{$key}]=$val";
                }
                $player->log(User::LOG_NOT_IN_VILLAGE, implode(',', $contents_arr));

                throw new Exception("You must be in your village to access this page!");
            }
        }
        if(isset($route->min_rank)) {
            if($player->rank_num < $route->min_rank) {
                throw new Exception("You are not a high enough rank to access this page!");
            }
        }
    }
}

Router::$routes = require __DIR__ . '/../config/routes.php';


