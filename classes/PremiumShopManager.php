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

    const EXCHANGE_MIN_YEN_PER_AK = 1.0;
    const EXCHANGE_MAX_YEN_PER_AK = 20.0;

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

    public function handlePremiumPruchase(int $amount, string $description): void {
        $this->player->premium_credits->subtract($amount, $description);
    }

    public function handlePremiumRefund(int $amount, string $description): void {
        $this->player->premium_credits->add($amount, $description);
    }

    public function handleMoneyPurchase(int $amount, string $description): void {
        $this->player->money->subtract($amount, $description);
    }
    public function handleMoneyRefund(int $amount, string $description): void {
        $this->player->money->add($amount, $description);
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
        $this->player->ninjutsu_skill = 0;
        $this->player->genjutsu_skill = 0;
        $this->player->taijutsu_skill = 0;
        $this->player->bloodline_skill = 0;
        $this->player->cast_speed = 0;
        $this->player->speed = 0;
        $this->player->intelligence = 0;
        $this->player->willpower = 0;

        //Reset Village Reputation Data
        $this->player->village_rep = 0;
        $this->player->weekly_rep = 0;
        $this->player->pvp_rep = 0;
        $this->player->mission_rep_cd = 0;

        //Bug fix: Elements previously was not cleared. -- Shadekun
        $this->player->elements = array();
        $this->player->missions_completed = array(); //Reset missions complete -- Hitori

        $this->player->exam_stage = 0;

        $this->player->updateData();

        $this->system->db->query("DELETE FROM `user_bloodlines` WHERE `user_id`='{$this->player->user_id}'");
        $this->system->db->query("UPDATE `daily_tasks` SET `last_reset`='0' WHERE `user_id`='{$this->player->user_id}'");
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
    
    // Name change
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
        
        if ($this->player->premium_credits->getAmount() < $ak_cost) {
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
        $this->handlePremiumPurchase($cost, "Username change");

        $this->system->message("You have changed your name to $new_name.");
        return ActionResult::succeeded();
    }

    // Stat transfer
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
        if ($this->player->premium_credits->getAmount() < $ak_cost) {
            throw new RuntimeException("You do not have enough Ancient Kunai!");
        }

        $yen_cost = $this->statTransferYenCost($transfer_amount, $transfer_speed);
        if ($this->player->money->getAmount() < $yen_cost) {
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

        $this->handlePremiumPurchase($ak_cost, "Transferred {$transfer_amount} {$original_stat} to {$target_stat}");
        $this->handleMoneyPurchase($yen_cost, "Transferred {$transfer_amount} {$original_stat} to {$target_stat}");

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

    // Element Change
    public function assertUserCanChangeElement(int $editing_element_index, string $new_element): void {
        $ak_cost = $this->costs['element_change'];

        //Player already has new element
        if (in_array($new_element, $this->player->elements)) {
            throw new RuntimeException("You already attuned to the $new_element element!");
        }
        //Check player's current element is valid
        switch ($this->player->elements[$editing_element_index]) {
            case Jutsu::ELEMENT_FIRE:
            case Jutsu::ELEMENT_WIND:
            case Jutsu::ELEMENT_LIGHTNING:
            case Jutsu::ELEMENT_EARTH:
            case Jutsu::ELEMENT_WATER:
                break;
            default:
                throw new RuntimeException("The $editing_element_index element ({$this->player->elements[$editing_element_index]}) is invalid!");
        }
        //Check that new element is valid
        switch ($new_element) {
            case Jutsu::ELEMENT_FIRE:
            case Jutsu::ELEMENT_WIND:
            case Jutsu::ELEMENT_LIGHTNING:
            case Jutsu::ELEMENT_EARTH:
            case Jutsu::ELEMENT_WATER:
                break;
            default:
                throw new RuntimeException("New element $new_element is invalid!");
        }

        if ($editing_element_index > $this->player->rank_num - 2) {
            throw new RuntimeException("Invalid element slot!");
        }

        //Check cost
        if ($this->player->premium_credits->getAmount() < $ak_cost) {
            throw new RuntimeException("You do not have enough Ancient Kunai!");
        }
    }
    
    public function changeElement(int $editing_element_index, string $new_element): ActionResult {
        $this->assertUserCanChangeElement($editing_element_index, $new_element);

        $ak_cost = $this->costs['element_change'];
        $previous_element = $this->player->elements[$editing_element_index];

        // Process purchase
        $this->handlePremiumPruchase(
            $ak_cost,
            "Changed element #{$editing_element_index} from {$this->player->elements[$editing_element_index]} to $new_element"
        );

        $this->player->getInventory();

        if (isset($this->player->elements[$editing_element_index])) {
            foreach ($this->player->jutsu as $jutsu) {
                if ($jutsu->element == $this->player->elements[$editing_element_index]) {
                    $this->player->removeJutsu($jutsu->id);
                }
            }
            $this->player->elements[$editing_element_index] = $new_element;
        }

        $this->player->updateData();
        $this->player->updateInventory();

        $message = '';
        switch ($new_element) {
            case Jutsu::ELEMENT_FIRE:
                $message = "With the image of blazing fires in your mind, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly one of the pedestals bursts into
                    fire, breaking your focus, and the elders smile and say:<br /
                    <br />\"Congratulations, you now have the Fire element. Fire is the embodiment of
                    consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
                    they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they
                    can extinguish your fires.\"
                    <br />";
                break;
            case Jutsu::ELEMENT_WIND:
                $message = "Picturing a tempestuous tornado, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. You feel a disturbance in the room and
                    suddenly realize that a small whirlwind has formed around one of the pedestals, and the elders smile and say:<br /
                    <br />\"Congratulations, you have the Wind element. Wind is the sharpest out of all chakra natures,
                    and can slice through anything when used properly. Your Wind chakra will be strong against
                    Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
                    because your wind only serves to fan their flames and make them stronger.\"
                    <br />";
                break;
            case Jutsu::ELEMENT_LIGHTNING:
                $message = "Imagining the feel of electricity coursing through your veins, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly you feel a charge in the air and
                    one of the pedestals begins to spark with crackling electricity, and the elders smile and say:<br />
                    <br />\"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
                    this element physically augment themselves to swiftly strike through almost anything. Your Lightning
                    jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
                    but you must be careful against Wind jutsu as they will dissipate your Lightning.\"
                    <br />";
                break;
            case Jutsu::ELEMENT_EARTH:
                $message = "Envisioning stone as hard as the temple you are sitting in, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly dirt from nowhere begins to fall off one of the
                    pedestals and the elders smile and say:<br />
                    <br />\"Congratulations, you have the Earth element. Earth
                    is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be
                    strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to
                    Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques.\"
                    <br />";
                break;
            case Jutsu::ELEMENT_WATER:
                $message = "With thoughts of splashing rivers flowing through your mind, you flow chakra from your stomach,
                    down through your legs and into the seal on the floor. Suddenly a small geyser erupts from one of
                    the pedestals, and the elders smile and say:<br />
                    <br />\"Congratulations, you have the Water element. Water is a versatile element that can control the flow
                    of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
                    Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless.\"
                    <br />";
                break;
        }

        $message .=  "<b style='color:green'>You have forgotten the {$previous_element} nature and all its
                jutsu and are now attuned to the {$new_element} nature.</b>";

        return ActionResult::succeeded($message);
    }

    // Gender Change
    public function assertUserCanChangeGender(string $new_gender): void {
        if ($this->player->premium_credits->getAmount() < $this->costs['gender_change']) {
            throw new RuntimeException("You do not have enough Ancient Kunai!");
        }
        if ($this->player->gender == $new_gender) {
            throw new RuntimeException("Your gender is already {$new_gender}!");
        }
        if (!in_array($new_gender, User::$genders, true)) {
            throw new RuntimeException("Invalid gender!");
        }
    }

    public function changeGender(string $new_gender): ActionResult {
        $this->assertUserCanChangeGender($new_gender);

        $this->handlePremiumPruchase($this->costs['gender_change'], "Gender change to {$new_gender}");
        $this->player->gender = $new_gender;
        $this->player->updateData();

        return ActionResult::succeeded("You have changed your gender to $new_gender.");
    }

    // Buying shards
    public function getPaypalUrl(): string {
        return $this->system->isDevEnvironment()
            ? "https://www.sandbox.paypal.com/cgi-bin/webscr"
            : "https://www.paypal.com/cgi-bin/webscr";
    }

    public function getPaypalBusinessId(): string {
        return $this->system->isDevEnvironment()
            ? 'lsmjudoka@lmvisions.com'
            : 'lsmjudoka05@yahoo.com';
    }

    public function getPaypalListenerUrl(): string {
        return $this->system->router->base_url . 'paypal_listener.php';
    }

    public function getAvailableClans(): array {
        $available_clans = [];

        if ($this->player->clan) {
            $this->system->db->query(
                "SELECT `clan_id`, `name` FROM `clans` WHERE `village` = '{$this->player->village->name}' AND `clan_id` != '{$this->player->clan->id}' AND `bloodline_only` = '0'"
            );

            while ($village_clans = $this->system->db->fetch()) {
                $available_clans[$village_clans['clan_id']] = stripslashes($village_clans['name']);
            }
        }

        if ($this->player->bloodline_id && $this->player->clan->id != $this->player->bloodline->clan_id) {
            $this->system->db->query(
                sprintf("SELECT `clan_id`, `name` FROM `clans` WHERE `clan_id` = '%d'", $this->player->bloodline->clan_id)
            );
            $result = $this->system->db->fetch();
            $available_clans[$result['clan_id']] = stripslashes($result['name']);
        }

        return $available_clans;
    }

}
