<?php

require_once __DIR__ . '/ActionResult.php';

class PremiumShopManager {
    const STAT_TRANSFER_STANDARD = 'standard';
    const STAT_TRANSFER_EXPEDITED = 'expedited';
    const STAT_TRANSFER_SUPER_EXPEDITED = 'super_expedited';

    const EXPEDITED_STAT_TRANSFER_SPEED_MULTIPLIER = 2;
    const SUPER_EXPEDITED_STAT_TRANSFER_SPEED_MULTIPLIER = 10;
    const SUPER_EXPEDITED_AK_COST_MULTIPLIER = 2;
    const SUPER_EXPEDITED_YEN_COST_MULTIPLIER = 2;

    public System $system;
    public User $player;

    public int $stat_transfer_points_per_min;
    public int $stat_transfer_points_per_ak;
    public float $expedited_stat_transfer_points_per_yen;

    public static int $free_stat_change_cooldown = 86400;
    public static int $free_stat_change_cooldown_hours = 24;

    public int $max_free_stat_change_amount = 100;

    public int $free_stat_change_cooldown_left;

    public array $costs;

    public function __construct(System $system, User $player) {
        $this->system = $system;
        $this->player = $player;

        // Costs
        $this->initCosts();

        // Stat transfers
        $this->initStatTransferVars();
    }

    private function initCosts(): void {
        $this->costs['name_change'] = 15;
        $this->costs['gender_change'] = 10;
        $this->costs['bloodline'][1] = 80;
        $this->costs['bloodline'][2] = 60;
        $this->costs['bloodline'][3] = 40;
        $this->costs['bloodline'][4] = 20;
        $this->costs['forbidden_seal_monthly_cost'] = [
            1 => 5,
            2 => 15
        ];
        $this->costs['forbidden_seal'] = [
            1 => [
                30 => $this->costs['forbidden_seal_monthly_cost'][1],
                60 => $this->costs['forbidden_seal_monthly_cost'][1] * 2,
                90 => $this->costs['forbidden_seal_monthly_cost'][1] * 3
            ],
            2 => [
                30 => $this->costs['forbidden_seal_monthly_cost'][2],
                60 => $this->costs['forbidden_seal_monthly_cost'][2] * 2,
                90 => $this->costs['forbidden_seal_monthly_cost'][2] * 3
            ]
        ];
        $this->costs['element_change'] = 10;
        $this->costs['village_change'] = 5 * $this->player->village_changes;
        $this->costs['clan_change'] = 5 * $this->player->clan_changes;
        if ($this->costs['village_change'] > 40) {
            $this->costs['village_change'] = 40;
        }
        if ($this->costs['clan_change'] > 40) {
            $this->costs['clan_change'] = 40;
        }

        $this->costs['reset_ai_battles'] = 10;
        $this->costs['reset_pvp_battles'] = 20;
    }

    private function initStatTransferVars(): void {
        $this->stat_transfer_points_per_min = 10;
        $this->stat_transfer_points_per_ak = 300;

        if ($this->player->rank_num >= 3) {
            $this->stat_transfer_points_per_min += 5;

            $this->stat_transfer_points_per_ak = 600;
        }
        if ($this->player->rank_num >= 4) {
            $this->stat_transfer_points_per_min += 5;

            $this->stat_transfer_points_per_ak = 1200;
        }

        $this->stat_transfer_points_per_min += $this->player->forbidden_seal->stat_transfer_boost;
        $this->stat_transfer_points_per_ak += $this->player->forbidden_seal->extra_stat_transfer_points_per_ak;

        $this->expedited_stat_transfer_points_per_yen = round($this->stat_transfer_points_per_ak / 1000, 5);

        // Free stat transfers
        self::$free_stat_change_cooldown_hours = self::$free_stat_change_cooldown / 3600;

        $this->free_stat_change_cooldown_left = $this->player->last_free_stat_change - (time() - self::$free_stat_change_cooldown);
    }

    public function assertUserCanReset(): void {
        if ($this->player->team) {
            throw new RuntimeException("You must leave your team before resetting!");
        }
        if ($this->player->clan_office) {
            throw new RuntimeException("You must resign from your clan office first!");
        }
        if (SenseiManager::isSensei($this->player->user_id, $this->system)) {
            throw new RuntimeException("You must resign from being a sensei first!");
        }
    }

    public function resetUser(): ActionResult {
        $this->assertUserCanReset();

        $this->player->level = 1;
        $this->player->rank_num = 1;
        $this->player->health = 100;
        $this->player->max_health = 100;
        $this->player->stamina = 100;
        $this->player->max_stamina = 100;
        $this->player->chakra = 100;
        $this->player->max_chakra = 100;
        $this->player->regen_rate = User::BASE_REGEN;
        $this->player->exp = User::BASE_EXP;
        $this->player->bloodline_id = 0;
        $this->player->bloodline_name = '';
        $this->player->clan = null;
        $this->player->clan_id = 0;
        $this->player->location = $this->player->village_location;
        $this->player->pvp_wins = 0;
        $this->player->pvp_losses = 0;
        $this->player->ai_wins = 0;
        $this->player->ai_losses = 0;
        $this->player->monthly_pvp = 0;
        $this->player->ninjutsu_skill = 10;
        $this->player->genjutsu_skill = 10;
        $this->player->taijutsu_skill = 10;
        $this->player->bloodline_skill = 0;
        $this->player->cast_speed = 5;
        $this->player->speed = 5;
        $this->player->intelligence = 5;
        $this->player->willpower = 5;

        //Bug fix: Elements previously was not cleared. -- Shadekun
        $this->player->elements = array();
        $this->player->missions_completed = array(); //Reset missions complete -- Hitori

        $this->player->exam_stage = 0;

        $this->player->updateData();

        $this->system->db->query("DELETE FROM `user_bloodlines` WHERE `user_id`='{$this->player->user_id}'");
        $this->system->db->query(
            "UPDATE `user_inventory` SET
                    `jutsu` = '',
                    `items` = '',
                    `bloodline_jutsu` = '',
                    `equipped_jutsu` = '',
                    `equipped_items` = ''
                    WHERE `user_id`='{$this->player->user_id}'"
        );

        return ActionResult::succeeded();
    }
    
    
    public function userNameChangeCost(string $new_name): int {
        $cost = $this->costs['name_change'];

        if ($this->player->free_username_changes > 0) {
            $cost = 0;
        }
        elseif (strtolower($this->player->user_name) == strtolower($new_name)) {
            $cost = 0;
        } 

        return $cost;
    }
    
    
    public function assertUserCanChangeName(string $new_name): void {
        $ak_cost = $this->userNameChangeCost($new_name);
        
        if ($this->player->getPremiumCredits() < $ak_cost) {
            throw new RuntimeException("You do not have enough Ancient Kunai!");
        }
        if (strlen($new_name) < User::MIN_NAME_LENGTH || strlen($new_name) > User::MAX_NAME_LENGTH) {
            throw new RuntimeException("New user name is to short/long! Please enter a name between "
                . User::MIN_NAME_LENGTH . " and " . User::MAX_NAME_LENGTH . " characters long.");
        }
        if (strtolower($this->player->user_name) == strtolower($new_name)) {
            throw new RuntimeException("Please select a different name than your current one.");
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $new_name)) {
            throw new RuntimeException("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
        }

        if ($this->system->explicitLanguageCheck($new_name)) {
            throw new RuntimeException("Inappropriate language is not allowed in usernames!");
        }

        $result = $this->system->db->query("SELECT `user_name` FROM `users` WHERE `user_name`='$new_name' LIMIT 1");
        if ($this->system->db->last_num_rows) {
            $result = $this->system->db->fetch();
            if (strtolower($result['user_name']) == strtolower($new_name) && $result['user_name'] != $this->player->user_name) {
                throw new RuntimeException("Username already in use!");
            }
        }
    }

    public function changeUserName(string $new_name): ActionResult {
        $cost = $this->userNameChangeCost($new_name);
        $this->assertUserCanChangeName($new_name);

        $free_name_changes_used = 0;
        if($this->player->free_username_changes > 0
            && strtolower($this->player->user_name) !== strtolower($new_name)
        ) {
            $free_name_changes_used = 1;
        }

        $this->system->db->query("UPDATE `users` SET 
            `user_name` = '{$new_name}', 
            `username_changes` = `username_changes` - {$free_name_changes_used}
           WHERE `user_id` = {$this->player->user_id} LIMIT 1;");
        $this->player->subtractPremiumCredits($cost, "Username change");

        $this->system->message("You have changed your name to $new_name.");
        return ActionResult::succeeded();
    }


    public function statTransferPremiumCreditCost(int $transfer_amount, string $transfer_speed): int {
        $is_free_stat_change =
            $transfer_amount <= $this->max_free_stat_change_amount
            && $this->free_stat_change_cooldown_left <= 0;

        if ($is_free_stat_change) {
            return 0;
        } else {
            switch($transfer_speed) {
                case self::STAT_TRANSFER_STANDARD:
                case self::STAT_TRANSFER_EXPEDITED:
                    return 1 + floor($transfer_amount / $this->stat_transfer_points_per_ak);
                case self::STAT_TRANSFER_SUPER_EXPEDITED:
                    return 1 + floor(
                    ($transfer_amount / $this->stat_transfer_points_per_ak) * self::SUPER_EXPEDITED_AK_COST_MULTIPLIER
                    );
                default:
                    throw new RuntimeException("Invalid transfer type!");
            }
        }
    }

    public function statTransferYenCost(int $transfer_amount, string $transfer_speed): int {
       switch($transfer_speed) {
           case self::STAT_TRANSFER_STANDARD:
               return 0;
           case self::STAT_TRANSFER_EXPEDITED:
               return round($transfer_amount / $this->expedited_stat_transfer_points_per_yen, -2);
           case self::STAT_TRANSFER_SUPER_EXPEDITED:
               return round(
                   ($transfer_amount / $this->expedited_stat_transfer_points_per_yen) * self::SUPER_EXPEDITED_YEN_COST_MULTIPLIER,
                   -2
               );
           default:
               throw new RuntimeException("Invalid transfer type!");
       }
    }

    public function assertUserCanTransferStat(
        string $original_stat,
        string $target_stat,
        int $transfer_amount,
        string $transfer_speed
    ): void {
        switch($transfer_speed) {
            case self::STAT_TRANSFER_STANDARD:
            case self::STAT_TRANSFER_EXPEDITED:
            case self::STAT_TRANSFER_SUPER_EXPEDITED:
                break;
            default:
                throw new RuntimeException("Invalid transfer type!");
        }

        if (!in_array($original_stat, $this->player->stats)) {
            throw new RuntimeException("Invalid original stat!");
        }
        if (!in_array($target_stat, $this->player->stats)) {
            throw new RuntimeException("Invalid target stat!");
        }

        // Check for same stat
        if ($original_stat == $target_stat) {
            throw new RuntimeException("You cannot transfer points to the same stat!");
        }

        // Transfer amount
        if ($transfer_amount < 1) {
            throw new RuntimeException("Invalid transfer amount!");
        }
        if ($transfer_amount > $this->player->{$original_stat}) {
            throw new RuntimeException("Invalid transfer amount!");
        }

        $ak_cost = $this->statTransferPremiumCreditCost($transfer_amount, $transfer_speed);
        if ($this->player->getPremiumCredits() < $ak_cost) {
            throw new RuntimeException("You do not have enough Ancient Kunai!");
        }

        $yen_cost = $this->statTransferYenCost($transfer_amount, $transfer_speed);
        if ($this->player->getMoney() < $yen_cost) {
            throw new RuntimeException("You do not have enough yen!");
        }
    }

    public function transferStat(
        string $original_stat,
        string $target_stat,
        int $transfer_amount,
        string $transfer_speed
    ): ActionResult {
        $this->assertUserCanTransferStat(
            original_stat: $original_stat,
            target_stat: $target_stat,
            transfer_amount: $transfer_amount,
            transfer_speed: $transfer_speed
        );

        $ak_cost = $this->statTransferPremiumCreditCost($transfer_amount, $transfer_speed);
        $yen_cost = $this->statTransferYenCost($transfer_amount, $transfer_speed);
        $time = $this->statTransferTime($transfer_amount, $transfer_speed);

        $this->player->subtractPremiumCredits($ak_cost, "Transferred {$transfer_amount} {$original_stat} to {$target_stat}");
        $this->player->subtractMoney($yen_cost, "Transferred {$transfer_amount} {$original_stat} to {$target_stat}");

        $exp = $transfer_amount * 10;
        $this->player->exp -= $exp;
        $this->player->{$original_stat} -= $transfer_amount;

        $this->player->stat_transfer_target_stat = $target_stat;
        $this->player->stat_transfer_amount = $transfer_amount;
        $this->player->stat_transfer_completion_time = time() + ($time * 60);

        if ($ak_cost == 0) {
            $this->player->last_free_stat_change = time();
        }

        $this->player->updateData();

        $new_notification = new NotificationDto(
            type: "stat_transfer",
            message: "Transferring {$transfer_amount} " . System::unSlug($original_stat) . " to " . System::unSlug($target_stat),
            user_id: $this->player->user_id,
            created: time(),
            duration: $time * 60,
            alert: false,
        );
        NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);

        return ActionResult::succeeded();
    }

    public function statTransferTime(int $transfer_amount, string $transfer_speed): int {
        switch($transfer_speed) {
            case self::STAT_TRANSFER_STANDARD:
                return $transfer_amount / $this->stat_transfer_points_per_min;
            case self::STAT_TRANSFER_EXPEDITED:
                return $transfer_amount /
                    ($this->stat_transfer_points_per_min * self::EXPEDITED_STAT_TRANSFER_SPEED_MULTIPLIER);
            case self::STAT_TRANSFER_SUPER_EXPEDITED:
                return $transfer_amount /
                    ($this->stat_transfer_points_per_min * self::SUPER_EXPEDITED_STAT_TRANSFER_SPEED_MULTIPLIER);
            default:
                throw new RuntimeException("Invalid transfer type!");
        }
    }
}