<?php

class VillageProposalDto {
    public function __construct(
        public string $proposal_id,
        public int $village_id,
        public int $user_id,
        public int $start_time,
        public ?int $end_time,
        public string $name,
        public ?string $result,
        public string $type,
        public ?int $target_village_id,
        public ?int $policy_id,
        public ?string $vote_time_remaining,
        public ?string $enact_time_remaining,
        public ?string $trade_data,
        public array $votes = []
    ) {}
}