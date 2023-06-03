<?php

class HeaderLinkDto {
    public function __construct(
        public string $title,
        public string $url,
    ) {}
}