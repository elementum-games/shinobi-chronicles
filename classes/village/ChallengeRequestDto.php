<?php

class ChallengeRequestDto {
    public function __construct(
        public int $request_id,
        public int $challenger_id,
        public int $seat_holder_id,
        public int $seat_id,
        public int $created_time,
        public ?int $accepted_time = null,
        public ?int $start_time = null,
        public ?int $end_time = null,
        public bool $seat_holder_locked,
        public bool $challenger_locked,
        public array $selected_times,
        public ?int $battle_id = null,
        public ?string $winner = null,
        public ?string $challenger_name = '',
        public ?string $challenger_avatar = '',
        public ?string $seat_holder_name = '',
        public ?string $seat_holder_avatar = '',
    ) {}
}