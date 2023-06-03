<?php

require __DIR__ . '/HeaderLinkDto.php';

class HeaderManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;

    }

    /**
     * @return HeaderLinkDto[]
     */
    public function getHeaderMenu() : array {
        $routes = Router::$routes;
        $return_arr = [];
        $return_arr[] = new HeaderLinkDto(
            title: "NEWS",
            url: $this->system->router->links['news'],
        );
        $return_arr[] = new HeaderLinkDto(
            title: "DISCORD",
            url: $this->system->router->links['discord'] . "target='_blank'",
        );
        $return_arr[] = new HeaderLinkDto(
            title: "MANUAL",
            url: $this->system->router->base_url . "manual.php",
        );
        $return_arr[] = new HeaderLinkDto(
            title: "GITHUB",
            url: $this->system->router->links['github'] . "target='_blank'",
        );
        $return_arr[] = new HeaderLinkDto(
            title: "RULES",
            url: $this->system->router->base_url . "rules.php",
        );
        $return_arr[] = new HeaderLinkDto(
            title: "SUPPORT",
            url: $this->system->router->base_url . "support.php",
        );
        return $return_arr;
    }
}