<?php

require __DIR__ . '/NavigationLinkDto.php';

class NavigationAPIManager {
    public function __construct(
        public System $system,
        public array $routes,
        public ?User $player = null
    ){}

    /**
     * @return NavigationLinkDto[]
     */
    public function getUserMenu() : array {
        return $this->getMenuLinks(($this->system->USE_ROUTE_V2) ? RouteV2::MENU_USER : Route::MENU_USER);
    }

    /**
     * @return NavigationLinkDto[]
     */
    public function getActivityMenu(): array
    {
        return $this->getMenuLinks(($this->system->USE_ROUTE_V2) ? RouteV2::MENU_ACTIVITY : Route::MENU_ACTIVITY);
    }

    /**
     * @return NavigationLinkDto[]
     */
    public function getVillageMenu(): array
    {
        return $this->getMenuLinks(($this->system->USE_ROUTE_V2) ? RouteV2::MENU_VILLAGE : Route::MENU_VILLAGE);
    }
    /**
     * @return NavigationLinkDto[]
     */
    public function getStaffMenu(): array
    {
        if(!$this->system->USE_ROUTE_V2) {
            $return_arr = [];

            if ($this->player->isModerator() || $this->player->hasAdminPanel() || $this->player->isSupportStaff()) {
                if ($this->player->isSupportStaff()) {
                    $return_arr[] = new NavigationLinkDto(
                        title: "Support Panel",
                        url: $this->system->router->base_url . "?id=30",
                        active: true,
                        id: Router::PAGE_IDS['support'],
                    );
                }
                if ($this->player->isModerator()) {
                    $return_arr[] = new NavigationLinkDto(
                        title: "Mod Panel",
                        url: $this->system->router->base_url . "?id=16",
                        active: true,
                        id: Router::PAGE_IDS['mod'],
                    );
                }
                if ($this->player->hasAdminPanel()) {
                    $return_arr[] = new NavigationLinkDto(
                        title: "Admin Panel",
                        url: $this->system->router->base_url . "?id=17",
                        active: true,
                        id: Router::PAGE_IDS['admin'],
                    );
                }
            }

            return $return_arr;
        }
        else {
            $this->getMenuLinks(menu_name: RouteV2::MENU_STAFF);
        }
    }

    public function getMenuLinks(string $menu_name): array {
        $return_arr = array();

        // Update condition pages
        if(!is_null($this->player)) {
            if($this->system->USE_ROUTE_V2) {
                if ($this->player->clan) {
                    $this->routes['clan']->menu = Route::MENU_VILLAGE;
                }
                if ($this->player->rank_num >= 3) {
                    $this->routes['team']->menu = Route::MENU_USER;
                }
            }
            else {
                if ($this->player->clan) {
                    $this->routes[20]->menu = Route::MENU_VILLAGE;
                }
                if ($this->player->rank_num >= 3) {
                    $this->routes[24]->menu = Route::MENU_USER;
                }
            }
        }

        // Filter menu
        foreach($this->routes as $id => $page) {
            // No menu set or menu does not match current
            if(!isset($page->menu) || $page->menu != $menu_name) {
                continue;
            }
            // Requested page isn't available on production
            if($page->dev_only && !$this->system->isDevEnvironment()) {
                continue;
            }

            // Staff menu logic
            if($this->system->USE_ROUTE_V2 && $menu_name == RouteV2::MENU_STAFF) {
                // Failsafe, not a staff member
                if(!$this->player->staff_manager->isModerator() || !$this->player->staff_manager->hasAdminPanel() || !$this->player->staff_manager->isSupportStaff()) {
                    continue;
                }

                // Inadequate permissions
                if($page->user_check) {
                    if(!$page->user_check($this, $this->player)) {
                        continue;
                    }
                }
            }

            // Add link to navigation
            $page_key = ($this->system->USE_ROUTE_V2) ? RouteV2::ROUTE_PAGE_KEY : "id";
            $return_arr[] = new NavigationLinkDto(
                title: $page->title,
                url: $this->system->router->base_url . "?$page_key" . $id,
                active: true,
                id: $id
            );
        }

        return $return_arr;
    }

    /**
     * @return NavigationLinkDto[]
     */
    public static function getHeaderMenu(System $system): array
    {
        return [
            new NavigationLinkDto(
                title: "HOME",
                url: $system->router->base_url . "?home",
                active: true,
                id: 0,
            ),
            new NavigationLinkDto(
                title: "NEWS",
                url: $system->router->links['news'],
                active: true,
                id: 0,
            ),
            new NavigationLinkDto(
                title: "DISCORD",
                url: $system->router->links['discord'],
                active: true,
                id: 0,
            ),
            new NavigationLinkDto(
                title: "MANUAL",
                url: $system->router->base_url . "manual.php",
                active: true,
                id: 0,
            ),
            new NavigationLinkDto(
                title: "GITHUB",
                url: $system->router->links['github'],
                active: true,
                id: 0,
            ),
            new NavigationLinkDto(
                title: "RULES",
                url: $system->router->base_url . "rules.php",
                active: true,
                id: 0,
            ),
            new NavigationLinkDto(
                title: "SUPPORT",
                url: $system->router->base_url . "support.php",
                active: true,
                id: 0,
            ),

        ];
    }

    /**
     * @param System $system
     * @param User|null $player
     * @return NavigationAPIManager
     */
    public static function loadNavigationAPIManager(System $system, ?User $player = null): NavigationAPIManager {
        return new NavigationAPIManager(
            system: $system,
            routes: ($system->USE_ROUTE_V2) ? $system->routerV2->routes : Router::$routes,
            player: $player
        );
    }

}