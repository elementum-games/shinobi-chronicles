<?php

class Notification {
    public string $action_url;
    public string $title;
    public bool $critical;

    public function __construct(string $action_url, string $title, bool $critical = false) {
        $this->action_url = $action_url;
        $this->title = $title;
        $this->critical = $critical;
    }
}