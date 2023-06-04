<?php

class NavigationApiPresenter {
    public static function userMenuResponse(NavigationAPIManager $navigationManager): array {
        return array_map(
            function (NavigationLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $navigationManager->getUserMenu()
        );
    }

    public static function activityMenuResponse(NavigationAPIManager $navigationManager): array {
        return array_map(
            function(NavigationLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $navigationManager->getActivityMenu()
        );
    }

    public static function villageMenuResponse(NavigationAPIManager $navigationManager): array {
        return array_map(
            function (NavigationLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $navigationManager->getVillageMenu()
        );
    }

    public static function headerMenuResponse(NavigationAPIManager $navigationManager): array
    {
        return array_map(
            function (NavigationLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                    'active' => $link->active,
                    'id' => $link->id,
                ];
            },
            $navigationManager->getHeaderMenu()
        );
    }
}
