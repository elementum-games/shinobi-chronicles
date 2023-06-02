<?php

class SidebarApiPresenter {
    public static function userMenuResponse(SidebarManager $sidebarManager): array {
        return array_map(
            function (SidebarLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $sidebarManager->getUserMenu()
        );
    }

    public static function activityMenuResponse(SidebarManager $sidebarManager): array {
        return array_map(
            function(SidebarLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $sidebarManager->getActivityMenu()
        );
    }

    public static function villageMenuResponse(SidebarManager $sidebarManager): array {
        return array_map(
            function (SidebarLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $sidebarManager->getVillageMenu()
        );
    }
}
