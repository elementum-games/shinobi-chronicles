<?php

class ProposalManager {
    const PROPOSAL_COOLDOWN_HOURS = 1;

    /**
     * @param System $system
     * @param Player $player
     * @return ActionResult
     */
    public static function checkProposalCooldown($system, $player): ActionResult {
        if ($system->isDevEnvironment()) {
            return ActionResult::succeeded();
        }
        $last_proposal = self::getLastProposalData($system, $player);
        if ($system->db->last_num_rows > 0) {
            if ($last_proposal['start_time'] + self::PROPOSAL_COOLDOWN_HOURS * 3600 > time()) {
                $seconds_remaining = (self::PROPOSAL_COOLDOWN_HOURS * 3600) + $last_proposal['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return ActionResult::failed("Cannot submit another proposal for " . $time_remaining . ".");
            }
        }
        return ActionResult::succeeded();
    }

    private static function getLastProposalData($system, $player): array {
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `user_id` = {$player->user_id} ORDER BY `start_time` DESC LIMIT 1");
        $result = $system->db->fetch($query);
        if ($system->db->last_num_rows == 0) {
            return [];
        }
        return $result;
    }
}
