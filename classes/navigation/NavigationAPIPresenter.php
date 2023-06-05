<?php

class NavigationApiPresenter {
    public static function menuLinksResponse(array $navigationList): array
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
            $navigationList
        );
    }
}
