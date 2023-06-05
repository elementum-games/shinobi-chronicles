<?php

class NavigationApiPresenter {
    /**
     * @param NavigationLinkDto[] $menuLinks
     * @return array
     */
    public static function menuLinksResponse(array $menuLinks): array
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
            $menuLinks
        );
    }
}
