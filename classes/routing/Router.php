<?php
require_once __DIR__ . '/battle/Battle.php';
require_once __DIR__ . '/routing/Route.php';

class RouterV2 {
    public function __construct(
        private Database $db,
        public readonly string $base_url,
        public string $current_route, // Note: This will replace $self_link on all pages
        public array $current_route_variables,
        public readonly array $routes,
        public readonly array $links = [
            "github" => "https://github.com/elementum-games/shinobi-chronicles",
            "discord" => "https://discord.gg/Kx52dbXEf3",
        ],
    ){}

    /**
     * The standard for routing is storing the file api folder in slugged format
     * IMPORTANT: This standard should be adheared to for all API files
     * (e.g. Chat => chat, Forbidden Market => forbidden_market)
     * @param string $api_name
     * @return string
     * @throws RuntimeException
     */
    public function getApiLink(string $api_name): string {
        // Check if requested API exists
        if(!file_exists(__DIR__ . "/../api/$api_name.php")) {
            throw new RuntimeException("Invalid API: $api_name");
        }

        return $this->base_url . "/api/$api_name.php";
    }

    /**
     * Used to set $current_route for additioinal page navigation & forms
     * @param string $var_name
     * @param string $value
     * @return void
     * @throws RuntimeException
     */
    public function setCurrentRoute(string $var_name, string $value): void {
        // Validate navigation to different pages (e.g. profile, chat, etc.)
        if($var_name === RouteV2::ROUTE_PAGE_KEY && !isset($this->routes[$var_name])) {
            // Log error for unreported instances
            $this->db->query("INSERT INTO `error_logs`
                (`log_type`, `content`, `time`)
                VALUES
                ('set_route', '$var_name => $value', " . time() . ")
            ");

            // Visual error
            throw new RuntimeException("$value is an invalid " . RouteV2::ROUTE_PAGE_KEY);
        }

        // Variable already used, overwrite
        if(isset($this->current_route_variables[$var_name])) {
            $this->current_route_variables[$var_name] = $value;
        }
        // New variables
        else {
            $this->current_route_variables[$var_name] = $value;
        }

        $this->current_route = $this->base_url;
        $route_prepend = "?";
        foreach($this->current_route_variables as $name => $val) {
            $this->current_route .= $route_prepend . $name . "=" . $val;
            $route_prepend = "&";
        }
    }

    /**
     * @param Route $route
     * @param User  $player
     * @return void
     * @throws RuntimeException
     */
    public function assertRouteIsValid(Route $route, User $player): void {
        $system = $player->system;

        // Dev only page
        if($route->dev_only && !$system->isDevEnvironment()) {
            throw new RuntimeException("Invalid page!");
        }

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
                throw new RuntimeException("You cannot visit this page while in battle!");
            }
        }

        // Check for spar/fight PvP type, stop page if trying to load spar/battle while in NPC battle
        if(isset($route->battle_type)) {
            $result = $system->db->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1"
            );
            if($system->db->last_num_rows > 0) {
                $battle_type = $system->db->fetch($result)['battle_type'];
                if($battle_type != $route->battle_type) {
                    throw new RuntimeException("You cannot visit this page while in combat!");
                }
            }
        }

        // Check if challenge locked
        if (isset($route->challenge_lock_ok) && $route->challenge_lock_ok === false) {
            if ($player->locked_challenge > 0) {
                throw new RuntimeException("You are unable to access this page while locked-in for battle!");
            }
        }

        if(isset($route->user_check)) {
            if(!($route->user_check instanceof Closure)) {
                throw new RuntimeException("Invalid user check!");
            }

            $page_ok = $route->user_check->call($this, $player);

            if(!$page_ok) {
                throw new RuntimeException("");
            }
        }

        // Check location restrictions
        $player_location_type = TravelManager::getPlayerLocationType($system, $player);
        if ($player_location_type == TravelManager::LOCATION_TYPE_HOME_VILLAGE) {
            if (!$route->allowed_location_types[TravelManager::LOCATION_TYPE_HOME_VILLAGE]) {
                // Player is allowed in up to rank 3, then must go outside village
                if ($player->rank_num > 2) {
                    throw new RuntimeException("You cannot access this page while in a village!");
                }
            }
        }
        else if (!$route->allowed_location_types[$player_location_type]) {
            throw new RuntimeException("You can not access this page at your current location!");
        }

        if(isset($route->min_rank)) {
            if($player->rank_num < $route->min_rank) {
                throw new RuntimeException("You are not a high enough rank to access this page!");
            }
        }
    }
    
    /**
     * @param Database $db
     * @param string $base_rul
     * @return RouterV2
     */
    public static function load(Database $db, string $base_url): RouterV2 {
        return new RouterV2(
            db: $db,
            base_url: $base_url,
            current_route: $base_url,
            current_route_variables: [],
            routes: require(__DIR__ . "/../config/routes_v2.php")
        );
    }
}