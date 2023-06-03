<?php

class NavigationLinkDto {
    public function __construct(
        public string $title,
        public string $url,
        public bool $active,
        public int $id,
    ) {}
}