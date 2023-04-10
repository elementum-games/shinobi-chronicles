<?php

class Layout {
    public function __construct(
        public string $key,
        public string $heading,
        public string $header,
        public string $body_start,
        public string $top_menu,
        public string $side_menu_start,
        public string $village_menu_start,
        public string $action_menu_header,
        public string $staff_menu_header,
        public string $side_menu_end,
        public string $login_menu,
        public string $footer,
    ) {}

    public function renderStaticPageHeader(string $page_title): void {
        echo $this->heading;
        echo $this->top_menu;
        echo $this->header;
        echo str_replace("[HEADER_TITLE]", $page_title, $this->body_start);
    }

    public function renderStaticPageFooter(?User $player = null): void {
        if($player != null) {
            $this->renderSideMenu($player, $player->system->router);
        }
        else {
            echo $this->login_menu;
        }

        $this->renderFooter();
    }

    public function renderSideMenu(User $player, Router $router): void {
        $routes = Router::$routes;

        if($player->clan) {
            $routes[20]->menu = System::MENU_VILLAGE;
        }
        if($player->rank_num >= 3) {
            $routes[24]->menu = System::MENU_USER;
        }

        // NEW MESSAGE ALERT
        $playerInbox = new InboxManager($player->system, $player);
        $new_inbox_message = $playerInbox->checkIfUnreadMessages();
        $new_inbox_alerts = $playerInbox->checkIfUnreadAlerts();

        echo str_replace(
            "[side-menu-location-status-class]",
            $player->in_village ? 'sm-tmp-invillage' : 'sm-tmp-outvillage',
            $this->side_menu_start
        );

        foreach($routes as $id => $page) {
            if(!isset($page->menu) || $page->menu != System::MENU_USER) {
                continue;
            }

            $menu_alert_icon =  ($page->title === 'Inbox' && ($new_inbox_message || $new_inbox_alerts))
                ? 'sidemenu_new_message_alert'
                : null;

            echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page->title)."' 
                href='{$router->base_url}?id=$id' 
                class='{$menu_alert_icon}'>"
                . $page->title
                . "</a></li>";
        }


        // Activity Menu
        echo $this->action_menu_header;
        foreach($routes as $id => $page) {
            if(!isset($page->menu) || $page->menu != System::MENU_ACTIVITY) {
                continue;
            }

            $class = '';
            if($page->village_ok === Route::ONLY_IN_VILLAGE) {
                $class = 'only-allowed-in-village';
            }
            if($page->village_ok == Route::NOT_IN_VILLAGE && $player->rank_num > 2) {
                $class = 'not-allowed-in-village';
            }

            echo "<li>
                <a 
                    id='sideMenuOption-" . str_replace(' ', '', $page->title) . "' 
                    href='{$router->base_url}?id=$id'
                    class='{$class}'
                >" . $page->title . "</a>
            </li>";
        }

        // Village menu
        echo $this->village_menu_start;
        foreach($routes as $id => $page) {
            if(!isset($page->menu) || $page->menu != System::MENU_VILLAGE) {
                continue;
            }

            echo "<li>
                <a 
                    id='sideMenuOption-" . str_replace(' ', '', $page->title) . "' 
                    href='{$router->base_url}?id=$id'
                >" . $page->title . "</a>
            </li>";
        }

        // Staff Menu
        if($player->isModerator() || $player->hasAdminPanel() || $player->isSupportStaff()) {
            echo $this->staff_menu_header;
            if($player->isSupportStaff()) {
                echo "<li><a id='sideMenuOption-SupportPanel' href='{$router->base_url}?id=30'>Support Panel</a></li>";
            }
            if($player->isModerator()) {
                echo "<li><a id='sideMenuOption-ModPanel' href='{$router->base_url}?id=16'>Mod Panel</a></li>";
            }
            if($player->hasAdminPanel()) {
                echo "<li><a id='sideMenuOption-AdminPanel' href='{$router->base_url}?id=17'>Admin Panel</a></li>";
            }
        }

        //  timer
        $time_remaining = (System::LOGOUT_LIMIT * 60) - (time() - $player->last_login);
        $logout_time = System::timeRemaining($time_remaining, 'short', false, true) . " remaining";

        $logout_display = $player->isUserAdmin() ? "Disabled" : $logout_time;
        echo str_replace("<!--LOGOUT_TIMER-->", $logout_display, $this->side_menu_end);

        if($logout_display != "Disabled") {
            echo "<script type='text/javascript'>
                countdownTimer($time_remaining, 'logoutTimer');
            </script>";
        }
    }

    public function renderFooter(?float $page_load_time = null): void {
        echo str_replace(
            ['<!--[VERSION_NUMBER]-->', '<!--[PAGE_LOAD_TIME]-->'],
            [System::VERSION_NUMBER, $page_load_time ?? ""],
            $this->footer
        );
    }
}