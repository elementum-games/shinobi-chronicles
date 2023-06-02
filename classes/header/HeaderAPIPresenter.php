<?php

class HeaderApiPresenter {
    public static function headerMenuResponse(HeaderManager $headerManager): array {
        return array_map(
            function (HeaderLinkDto $link) {
                return [
                    'title' => $link->title,
                    'url' => $link->url,
                ];
            },
            $headerManager->getheaderMenu()
        );
    }
}
