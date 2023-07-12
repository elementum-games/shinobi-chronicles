<?php

class ActionResult {
    public bool $failed;

    public bool $succeeded;
    public string $error_message = "";
    public ?string $success_message = null;

    private function __construct(
        bool $succeeded,
        string $error_message = "",
        ?string $success_message = null
    ) {
        $this->succeeded = $succeeded;
        $this->failed = !$this->succeeded;

        $this->error_message = $error_message;
        $this->success_message = $success_message;
    }

    public static function failed(string $error_message): ActionResult {
        return new ActionResult(
            succeeded: false,
            error_message: $error_message
        );
    }

    public static function succeeded(?string $success_message = null): ActionResult {
        return new ActionResult(
            succeeded: true,
            success_message: $success_message
        );
    }
}