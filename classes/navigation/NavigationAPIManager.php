<?php

require __DIR__ . '/NavigationLinkDto.php';

class NavigationAPIManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;

    }

    /**
     * @return NavigationLinkDto[]
     */
    public function getUserMenu() : array {
        $routes = Router::$routes;
        if ($this->player->clan) {
            $routes[20]->menu = Route::MENU_VILLAGE;
        }
        if ($this->player->rank_num >= 3) {
            $routes[24]->menu = Route::MENU_USER;
        }

        $return_arr = [];
        foreach ($routes as $id => $page) {
            if (!isset($page->menu) || $page->menu != Route::MENU_USER) {
                continue;
            }

            $return_arr[] = new NavigationLinkDto(
                title: $page->title,
                url: $this->system->router->base_url . "?id=" . $id,
                active: true,
                id: $id,
            );
        }
        /*$return_arr[] = new NavigationLinkDto(
            title: "Logout",
            url: $this->system->router->base_url . "?logout=1",
            active: true,
            id: 0,
        );*/
        return $return_arr;
    }

    /**
     * @return NavigationLinkDto[]
     */
    public function getActivityMenu(): array
    {
        $routes = Router::$routes;
        $linkList = [];
        if ($this->player->clan) {
            $routes[20]->menu = Route::MENU_VILLAGE;
        }
        if ($this->player->rank_num >= 3) {
            $routes[24]->menu = Route::MENU_USER;
        }

        $return_arr = [];
        foreach ($routes as $id => $page) {
            if (!isset($page->menu) || $page->menu != Route::MENU_ACTIVITY) {
                continue;
            }

            $return_arr[] = new NavigationLinkDto(
                title: $page->title,
                url: $this->system->router->base_url . "?id=" . $id,
                active: true,
                id: $id,
            );
            /*if ($page->village_ok === Route::ONLY_IN_VILLAGE) {
                $class = 'only-allowed-in-village';
            }
            if ($page->village_ok === Route::NOT_IN_VILLAGE && $this->player->rank_num > 2) {
                $class = 'not-allowed-in-village';
            }*/

        }
        return $return_arr;
    }

    /**
     * @return NavigationLinkDto[]
     */
    public function getVillageMenu(): array
    {
        $routes = Router::$routes;
        if ($this->player->clan) {
            $routes[20]->menu = Route::MENU_VILLAGE;
        }
        if ($this->player->rank_num >= 3) {
            $routes[24]->menu = Route::MENU_USER;
        }

        $return_arr = [];
        foreach ($routes as $id => $page) {
            if (!isset($page->menu) || $page->menu != Route::MENU_VILLAGE) {
                continue;
            }

            $return_arr[] = new NavigationLinkDto(
                title: $page->title,
                url: $this->system->router->base_url . "?id=" . $id,
                active: true,
                id: $id,
            );
        }
        return $return_arr;
    }
    /**
     * @return NavigationLinkDto[]
     */
    public function getStaffMenu(): array
    {
        $routes = Router::$routes;
        if ($this->player->clan) {
            $routes[20]->menu = Route::MENU_VILLAGE;
        }
        if ($this->player->rank_num >= 3) {
            $routes[24]->menu = Route::MENU_USER;
        }
        $return_arr = [];

        if ($this->player->isModerator() || $this->player->hasAdminPanel() || $this->player->isSupportStaff()) {
            if ($this->player->isSupportStaff()) {
                $return_arr[] = new NavigationLinkDto(
                    title: "Support Panel",
                    url: $this->system->router->base_url . "?id=30",
                    active: true,
                    id: 30,
                );
            }
            if ($this->player->isModerator()) {
                $return_arr[] = new NavigationLinkDto(
                    title: "Mod Panel",
                    url: $this->system->router->base_url . "?id=16",
                    active: true,
                    id: 16,
                );
            }
            if ($this->player->hasAdminPanel()) {
                $return_arr[] = new NavigationLinkDto(
                    title: "Admin Panel",
                    url: $this->system->router->base_url . "?id=17",
                    active: true,
                    id: 17,
                );
            }
        }

        return $return_arr;
    }

    /**
     * @return NavigationLinkDto[]
     */
    public static function getHeaderMenu(System $system): array
    {
        $routes = Router::$routes;
        $return_arr = [];
        $return_arr[] = new NavigationLinkDto(
            title: "NEWS",
            url: $system->router->links['news'],
            active: true,
            id: 0,
        );
        $return_arr[] = new NavigationLinkDto(
            title: "DISCORD",
            url: $system->router->links['discord'],
            active: true,
            id: 0,
        );
        $return_arr[] = new NavigationLinkDto(
            title: "MANUAL",
            url: $system->router->base_url . "manual.php",
            active: true,
            id: 0,
        );
        $return_arr[] = new NavigationLinkDto(
            title: "GITHUB",
            url: $system->router->links['github'],
            active: true,
            id: 0,
        );
        $return_arr[] = new NavigationLinkDto(
            title: "RULES",
            url: $system->router->base_url . "rules.php",
            active: true,
            id: 0,
        );
        $return_arr[] = new NavigationLinkDto(
            title: "SUPPORT",
            url: $system->router->base_url . "support.php",
            active: true,
            id: 0,
        );
        return $return_arr;
    }

}