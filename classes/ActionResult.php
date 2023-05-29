<?php

class ActionResult {
    public bool $failed;

    private function __construct(
        public bool $succeeded,
        public string $error_message = "",
    ) {
        $this->failed = !$this->succeeded;
    }

    public static function failed(string $error_message): ActionResult {
        return new ActionResult(
            succeeded: false,
            error_message: $error_message
        );
    }

    public static function succeeded(): ActionResult {
        return new ActionResult(succeeded: true);
    }
}