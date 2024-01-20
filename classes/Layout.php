<?php

require_once __DIR__ . '/Route.php';
require_once __DIR__ . "/navigation/NavigationAPIPresenter.php";
require_once __DIR__ . '/navigation/NavigationAPIManager.php';
require_once __DIR__ . '/notification/Notifications.php';

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

    public function renderBeforeContentHTML(
      System $system,
      ?User $player,
      string $page_title,
      bool $render_header = true,
      bool $render_sidebar = true,
      bool $render_topbar = true,
      bool $render_content = true
    ): void {
        if($this->usesV2Interface()) {
            echo $this->heading;
            if (isset($player)) {
                if ($player->getSidebarPosition() == 'right') {
                    echo "<link rel='stylesheet' type='text/css' href='{$system->getCssFileLink("style/configuration/sidebar_right.css")}' />";
                }
            }

            if ($render_header) {
                require 'templates/header.php';
            }
            if ($render_content) {
                echo "<div id='container'>";
            }

            if(isset($player)) {
                if ($render_sidebar) {
                    require 'templates/sidebar.php';
                } else {
                    echo "<link rel='stylesheet' type='text/css' href='{$system->getCssFileLink("style/configuration/sidebar_none.css")}' />";
                }
                if ($render_content) {
                    echo '<div id="content_wrapper">';
                }
                if ($render_topbar) {
                    require 'templates/topbar.php';
                }
            }

            if ($render_content) {
                echo str_replace("[HEADER_TITLE]", $page_title, $this->body_start);

                if ($player != null && !$player->global_message_viewed) {
                    $global_message = $system->fetchGlobalMessage();
                    $this->renderGlobalMessage($system, $global_message);
                }
            }

        }
        else {
            echo $this->heading;
            echo $this->top_menu;
            echo $this->header;

            if($player != null) {
                $notifications = Notifications::getNotifications($system, $player);

                echo "<div id='notifications'>";
                $this->renderLegacyNotifications($notifications);
                echo "</div>";

                if($player->train_time) {
                    $this->renderTrainingDisplay($player);
                }
            }

            echo str_replace("[HEADER_TITLE]", $page_title, $this->body_start);

            if($player != null) {
                if(!$player->global_message_viewed) {
                    $global_message = $system->fetchGlobalMessage();
                    $this->renderGlobalMessage($system, $global_message);
                }
            }
        }
    }

    public function renderAfterContentHTML(System $system, ?User $player, ?float $page_load_time = null, bool $render_content = true, bool $render_footer = true, bool $render_hotbar = true): void {
        if($this->usesV2Interface()) {
            if (!$render_content) {
                echo "</body></html>";
                return;
            }
            echo "</div>";
            echo "</div>";

            if($player != null) {
                echo "</div>";
                if ($system->isDevEnvironment()) {
                    if ($render_hotbar) {
                        require 'templates/hotbar.php';
                    }
                }
            }

            if ($render_footer) {
                $this->renderFooter($page_load_time);
            }
        }
        else {
            // Display side menu and footer
            if($player != null) {
                $this->renderSideMenu($player, $system->router);
            }
            else {
                echo str_replace('<!--CAPTCHA-->', '', $this->login_menu);
            }

            echo str_replace('<!--[VERSION_NUMBER]-->', System::VERSION_NUMBER, $this->footer);
        }
    }

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
            $routes[20]->menu = Route::MENU_VILLAGE;
        }
        if($player->rank_num >= 3) {
            $routes[24]->menu = Route::MENU_USER;
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
            if(!isset($page->menu) || $page->menu != Route::MENU_USER) {
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
            if(!isset($page->menu) || $page->menu != Route::MENU_ACTIVITY) {
                continue;
            }

            $class = '';
            if($page->village_ok === Route::ONLY_IN_VILLAGE) {
                $class = 'only-allowed-in-village';
            }
            if($page->village_ok === Route::NOT_IN_VILLAGE && $player->rank_num > 2) {
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
            if(!isset($page->menu) || $page->menu != Route::MENU_VILLAGE) {
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

    public function renderGlobalMessage(System $system, array $global_message): void {
        $clear_message_url = isset($_GET['id'])
            ? $system->router->base_url . "?id=" . (int)$_GET['id'] . "&clear_message=1"
            : $system->router->base_url . "?clear_message=1";

        echo "<table class='table globalMessage'>
            <tr><th colspan='2'>Global message</th></tr>
            <tr><td style='text-align:center;' colspan='2'>"
                . $system->html_parse($global_message['message'])
            . "</td></tr>
            <tr>
                <td style='width: 50px;' class='newsFooter'>
                    <a class='link' href='$clear_message_url'>Dismiss</a>
                </td>
                <td class='newsFooter'>" . $global_message['time'] . "</td>
            </tr>
        </table>";
    }

    public function renderTrainingDisplay(User $player) {
        $display = "";

        if(str_contains($player->train_type, 'jutsu:')) {
            $train_type = str_replace('jutsu:', '', $player->train_type);
            $display .= "<p class='trainingNotification'>Training: " . System::unSlug($train_type) . "<br />" .
                "<span id='trainingTimer'>"
                    . System::timeRemaining($player->train_time - time(), 'short', false, true)
                    . " remaining</span></p>";
        }
        else {
            $display .= "<p class='trainingNotification'>Training: " . System::unSlug($player->train_type) . "<br />" .
                "<span id='trainingTimer'>"
                . System::timeRemaining($player->train_time - time(), 'short', false, true)
                . " remaining</span></p>";
        }

        $display .= "<script type='text/javascript'>
                let train_time = " . ($player->train_time - time()) . ";
                setTimeout(()=>{titleBarFlash();}, train_time * 1000);
            </script>";

        echo $display;
    }

    /**
     * @param Notification[] $notifications
     * @return void
     */
    public function renderLegacyNotifications(array $notifications): void {
        if($this->usesV2Interface()) {
            return;
        }

        if($this->key == 'shadow_ribbon' || $this->key === 'blue_scroll') {
            if(count($notifications) > 1) {
                echo "<img class='slideButtonLeft' onclick='slideNotificationLeft()' src='./images/left_arrow.png' />";
            }

            echo "<div id='notificationSlider'>";

            foreach($notifications as $id => $notification) {
                $extra_class_names = $notification->critical ? 'red' : '';
                echo "<p class='notification' data-notification-id='$id'>
                        <a class='link {$extra_class_names}' href='{$notification->action_url}'>{$notification->title}</a>
                    </p>";
            }

            echo "</div>";
            if(count($notifications) > 1) {
                echo "<img class='slideButtonRight' onclick='slideNotificationRight()' src='./images/right_arrow.png' />";
            }

        }
        else if($this->key == 'geisha') {
            foreach($notifications as $id => $notification) {
                $extra_class_names = $notification->critical ? 'red' : '';
                echo "<p class='notification' style='margin-top:5px;margin-bottom:10px;'>
                        <a class='link {$extra_class_names}' href='{$notification->action_url}'>{$notification->title}</a>
                    </p>";
            }
        }
        else {
            echo "<div style='margin:0;border:1px solid #AAAAAA;border-radius:inherit;'>
                    <div class='header'>
                    Notifications
                    </div>";

            foreach($notifications as $id => $notification) {
                $extra_class_names = $notification->critical ? 'red' : '';
                echo "<p class='notification'>
                        <a class='link {$extra_class_names}' href='{$notification->action_url}'>{$notification->title}</a>
                    </p>";
            }
            echo "</div>";
        }
    }

    public function usesV2Interface(): bool {
        return $this->key == 'new_geisha' || $this->key == 'sumu';
    }

    public static function renderPage(
        System $system, 
        ?User $player,
        string $page_title, ?int $page_id = null, ?string $page_name = null,
        bool $render_header = true, bool $render_sidebar = true,
        bool $render_topbar = true, bool $render_content = true,
        ?int $page_load_start = null, bool $render_footer = true,
        bool $render_hotbar = false
    ) {
        // Rendering by ID will take precedence over rendering by name
        $render_page = $page_id ?? $page_name;
        $system->layout->renderBeforeContentHTML(
            system: $system, player: $player, page_title: $page_title,
            render_header: $render_header, render_sidebar: $render_sidebar,
            render_topbar: $render_topbar, render_content: $render_content
        );

        // Legacy event display
        if($system->event != null && !$system->layout->usesV2Interface() && $player != null) {
            require (__DIR__ . '/../templates/temp_event_header.php');
        }

        // Render page by id
        if(is_int($render_page)) {
            $route = Router::$routes[$render_page];
            require (__DIR__ . '/../pages/' . $route->file_name);
            ($route->function_name)();
        }
        // Render page by name
        // TODO: Make home page render based on ID
        else {
            switch($render_page) {
                default:
                    // Render home page
                    require_once (__DIR__ . '/../home.php');
                    require_once (__DIR__ . '/../login.php');
                    require_once (__DIR__ . '/../new_register.php');
                    require (__DIR__ . '/../templates/home.php');
            }
        }

        //Calc page load time
        $page_load_time = ($page_load_start) ? microtime(as_float: true) - $page_load_start : $page_load_start;
        $system->layout->renderAfterContentHTML(
            system: $system, player: $player, page_load_time: $page_load_time,
            render_content: $render_content, render_footer: $render_footer,
            render_hotbar: $render_hotbar
        );
    }
}