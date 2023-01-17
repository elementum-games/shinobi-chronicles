<?php

class InboxUser {
    public int $max_convos_allowed;

    public function __construct(
        public int $user_id,
        public string $user_name,
        public string $avatar_link,
        public ?array $forbidden_seal,
        public $staff_level,
        public array $blocked_ids
    ) {
        $this->max_convos_allowed = Inbox::maxConvosAllowed($this->forbidden_seal, $this->staff_level);
    }

    public function getConvoCount(System $system): int {
        return Inbox::conversationCountForUser($system, $this->user_id);
    }
}
