<?php

class VillageSeatDto {
    public function __construct(
        public ?string $seat_key,
        public ?int $seat_id,
        public ?int $user_id,
        public ?int $village_id,
        public ?string $seat_type,
        public ?string $seat_title,
        public ?int $seat_start,
        public ?string $user_name,
        public ?string $avatar_link,
        public ?bool $is_provisional,
    ) {}
}