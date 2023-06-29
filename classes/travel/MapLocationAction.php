<?php

class MapLocationAction {
    public function __construct(
        public string $action_url = "",
        public string $action_message = "",
    ) {}
}