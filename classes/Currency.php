<?php
class Currency {
    // Standard Currecies
    const TYPE_MONEY = 'money';
    const TYPE_PREMIUM_CREDITS = 'premium_credits';
    const TYPE_PREMIUM_CREDITS_PURCHASED = 'premium_credits_purchased';
    const TYPE_TOKEN = 'tokens';

    const MONEY_NAME = "Yen";
    const PREMIUM_NAME = "Ancient Kunai";
    const PREMIUM_PURCHASED_NAME = "AK Purchased";
    const TOKEN_NAME = "Ancient Shurkien";

    const MONEY_SYMBOL = "&yen;";
    const PREMIUM_SYMBOL = "AK";
    const PREMIUM_PURCHASED_SYMBOL = "AKP";
    const TOKEN_SYMBOL = "AS";

    const BASE_YEN_GAIN = 30;
    const BASE_YEN_COST = 15;
    const MAX_TOKENS = 0;

    public array $valid_currency_types;
    public string $name;
    public string $symbol;

    // Faction Currencies
    const TYPE_AYAKASHI_FAVOR = "ayakashi_favor";

    const AYAKASHI_NAME = "Ayakashi's Favor";

    const AYAKASHI_SYMBOL = "AF";

    // Yen gain controllers
    const MISSION_RANK_D_MULTIPLIER = 2;
    const MISSION_RANK_C_MULTIPLIER = 3;
    const MISSION_RANK_B_MULTIPLIER = 4;
    const MISSION_RANK_A_MULTIPLIER = 5;
    const MISSION_RANK_S_MULTIPLIER = 6;

    // Special mission battles
    const SPECIAL_MISSION_EASY_MOD = 1;
    const SPECIAL_MISSION_NORMAL_MOD = 1.5;
    const SPECIAL_MISSION_HARD_MOD = 2;
    const SPECIAL_MISSION_NIGHTMARE_MOD = 3;
    // Special mission completions
    const SPECIAL_MISSION_EASY_MULTIPLIER = 1;
    const SPECIAL_MISSION_NORMAL_MULTIPLIER = 2;
    const SPECIAL_MISSION_HARD_MULTIPLIER = 3;
    const SPECIAL_MISSION_NIGHTMARE_MULTIPLIER = 4;

    // Ramen
    const RAMEN_VEGETABLE_MULTIPLIER = 1.5;
    const RAMEN_PORK_MULTIPLIER = 3;
    const RAMEN_DELUXE_MULTIPLIER = 6;
    const RAMEN_ARENA_MULTIPLIER = 3;

    // Jutsu
    const JUTSU_MULTIPLER = 10;

    public function __construct(
        // Defined members
        public System $system,
        public string $type,
        private int $user_id,
        private int $amount,
        public ?UserDailyTasks &$userDailyTasks = null,
        public ?int $max_amount = null
    ) {
        $this->valid_currency_types = self::getValidCurrencies();
        // Validate currency type
        if(!in_array($this->type, $this->valid_currency_types)) {
            throw new RuntimeException("Invalid currency type $this->type!");
        }

        $this->name = self::getCurrencyName($this->type);
        $this->symbol = self::getCurrencySymbol($this->type);
    }

    // Process currency increase, set amount and log currency
    public function add(int $amount, string $description, bool $increment_daily_task = true, bool $log = true): void {
        if($increment_daily_task && $this->type == self::TYPE_MONEY && $this->userDailyTasks instanceof UserDailyTasks) {
            $this->userDailyTasks->progressTask(DailyTask::ACTIVITY_EARN_MONEY, $amount);
        }
        if(!is_null($this->max_amount) && $this->amount + $amount > $this->max_amount) {
            $amount = $this->max_amount - $this->amount;
        }
        $this->set($this->amount + $amount, $description, $log);
    }

    // Process currency reduction, set amount and log transaction
    public function subtract(int $amount, string $description, bool $log = true): void {
        if($this->amount - $amount < 0) {
            throw new RuntimeException("Not enough " . System::unSlug($this->type) . "!");
        }
        $this->set($this->amount - $amount, $description, $log);
    }

    // Set currency to new amount and log transaction
    private function set(int $new_amount, string $description, bool $log): void {
        if($log) {
            $this->system->currencyLog(
                character_id: $this->user_id,
                currency_type: $this->type,
                previous_balance: $this->amount,
                new_balance: $new_amount,
                transaction_amount: $new_amount - $this->amount,
                transaction_description: $description
            );
        }
        $this->amount = $new_amount;
    }

    // Manual currency log
    public function manualLog(int $new_amount, int $old_amount, string $description): void {
        $this->system->currencyLog(
            character_id: $this->user_id,
            currency_type: $this->type,
            previous_balance: $old_amount,
            new_balance: $new_amount,
            transaction_amount: $new_amount - $old_amount,
            transaction_description: $description
        );
    }

    // Return currnt amount of currency
    public function getAmount(): int {
        return $this->amount;
    }

    public function getFormattedCurrency(): string {
        if($this->type == self::TYPE_MONEY) {
            return $this->symbol . self::formatNumber($this->amount);
        }
        return self::formatNumber($this->amount);
    }

    /** Currency Conventions **/
    public static function getValidCurrencies(): array {
        return [
            self::TYPE_MONEY,
            self::TYPE_PREMIUM_CREDITS,
            self::TYPE_PREMIUM_CREDITS_PURCHASED,
            self::TYPE_TOKEN,

            self::TYPE_AYAKASHI_FAVOR
        ];
    }

    public static function formatNumber(int $num): string {
        return number_format($num);
    }

    public static function getCurrencyName(string $type): string {
        return match ($type) {
            self::TYPE_MONEY => self::MONEY_NAME,
            self::TYPE_PREMIUM_CREDITS => self::PREMIUM_NAME,
            self::TYPE_PREMIUM_CREDITS_PURCHASED => self::PREMIUM_PURCHASED_NAME,
            self::TYPE_TOKEN => self::TOKEN_NAME,

            self::TYPE_AYAKASHI_FAVOR => self::AYAKASHI_NAME,
            default => '???',
        };
    }

    public static function getCurrencySymbol(string $type): string {
        return match ($type) {
            self::TYPE_MONEY => self::MONEY_SYMBOL,
            self::TYPE_PREMIUM_CREDITS => self::PREMIUM_SYMBOL,
            self::TYPE_PREMIUM_CREDITS_PURCHASED => self::PREMIUM_PURCHASED_SYMBOL,
            self::TYPE_TOKEN => self::TOKEN_SYMBOL,

            self::TYPE_AYAKASHI_FAVOR => self::AYAKASHI_SYMBOL,
            default => '???',
        };
    }

    /** Calculate Yen Gains **/
    public static function calcRawYenGain(int $rank_num, int $multiplier): int {
        return ceil(((self::BASE_YEN_GAIN * $rank_num) + pow($rank_num+1, 2)) * $multiplier);
    }

    public static function roundYen(int $num, int $multiple_of): int {
        return $multiple_of * round($num / $multiple_of);
    }

    public static function getRoundedYen(int $rank_num, int $multiplier, int $multiple_of): int {
        return self::roundYen(
            num: self::calcRawYenGain($rank_num, $multiplier),
            multiple_of: $multiple_of
        );
    }

    /** Mission Gains **/
    public static function calcMissionMoneyGain(int $user_rank, int $mission_rank, int $mission_yen_round): int {
        match($mission_rank) {
            Mission::RANK_C => $multiplier = self::MISSION_RANK_C_MULTIPLIER,
            Mission::RANK_B => $multiplier = self::MISSION_RANK_B_MULTIPLIER,
            Mission::RANK_A => $multiplier = self::MISSION_RANK_A_MULTIPLIER,
            Mission::RANK_S => $multiplier = self::MISSION_RANK_S_MULTIPLIER,
            default => $multiplier = self::MISSION_RANK_D_MULTIPLIER
        };

        return self::getRoundedYen(
            rank_num: $user_rank,
            multiplier: $multiplier,
            multiple_of: $mission_yen_round
        );
    }

    public static function calcSpecialMissionBattleGain(int $user_rank, string $difficulty): int {
        $base_yen_per_battle = SpecialMission::BATTLE_BASE_YEN * ($user_rank+1);
        // Difficulty modifier
        match($difficulty) {
            SpecialMission::DIFFICULTY_NORMAL => $yen_gain = floor($base_yen_per_battle * self::SPECIAL_MISSION_NORMAL_MOD),
            SpecialMission::DIFFICULTY_HARD => $yen_gain = floor($base_yen_per_battle * self::SPECIAL_MISSION_HARD_MOD),
            SpecialMission::DIFFICULTY_NIGHTMARE => $yen_gain = floor($base_yen_per_battle * self::SPECIAL_MISSION_NIGHTMARE_MOD),
            default => $yen_gain = floor($base_yen_per_battle * self::SPECIAL_MISSION_EASY_MOD)
        };

        return self::roundYen(
            num: $yen_gain,
            multiple_of: SpecialMission::BATTLE_ROUND_MONEY_TO
        );
    }

    public static function getSpecialMissionMultiplier(string $difficulty): int {
        return match($difficulty) {
            SpecialMission::DIFFICULTY_NORMAL => self::SPECIAL_MISSION_NORMAL_MULTIPLIER,
            SpecialMission::DIFFICULTY_HARD => self::SPECIAL_MISSION_HARD_MULTIPLIER,
            SpecialMission::DIFFICULTY_NIGHTMARE => self::SPECIAL_MISSION_NIGHTMARE_MULTIPLIER,
            default => self::SPECIAL_MISSION_EASY_MULTIPLIER
        };
    }

    /** Dynamic Costs **/
    public static function calcRawYenCost(int $rank_num, float $multiplier): int {
        return floor(((self::BASE_YEN_COST * $rank_num) + pow($rank_num+1, 2)) * $multiplier);
    }
    public static function calcRamenCost(int $rank_num, string $ramen_type, bool $arena): int {
        $round_yen_to = ($arena) ? 50 : 20;
        $ramen_cost = match($ramen_type) {
            Item::RAMEN_TYPE_PORK => self::calcRawYenCost(rank_num: $rank_num, multiplier: self::RAMEN_PORK_MULTIPLIER),
            Item::RAMEN_TYPE_DELUXE => self::calcRawYenCost(rank_num: $rank_num, multiplier: self::RAMEN_DELUXE_MULTIPLIER),
            default => self::calcRawYenCost(rank_num: $rank_num, multiplier: self::RAMEN_VEGETABLE_MULTIPLIER),
        };
        if($arena) {
            $ramen_cost *= self::RAMEN_ARENA_MULTIPLIER;
        }
        return self::roundYen(
            num: $ramen_cost,
            multiple_of: $round_yen_to
        );
    }

    public static function calcJutsuScrollCost(int $jutsu_rank, float $jutsu_power, float $effect_amount): int {
        $effect_multiplier = ($effect_amount) ? 1 + round($effect_amount / 5) : 1;
        return self::roundYen(
            num: self::calcRawYenCost(
                rank_num: $jutsu_rank,
                multiplier: $jutsu_power
            ) * self::JUTSU_MULTIPLER * $effect_multiplier,
            multiple_of: 5
        );
    }

    /** Load Currency from DB **/
    public static function loadFromDb(System $system, int $user_id, string $type, int $amount, ?UserDailyTasks $userDailyTasks = null, ?int $max_amount = null): Currency {
        return new Currency(
            system: $system,
            type: $type,
            user_id: $user_id,
            amount: $amount,
            userDailyTasks: $userDailyTasks,
            max_amount: $max_amount
        );
    }

    public static function fetchUserCurrencies(System $system, int $user_id, array $user_data): array {
        $result = $system->db->query("SELECT * FROM `user_currency` WHERE `user_id`=$user_id");
        if(!$system->db->last_num_rows) {
            $system->db->query("INSERT INTO `user_currency`
                (
                    `user_id`,
                    `" . self::TYPE_MONEY . "`,
                    `" . self::TYPE_PREMIUM_CREDITS . "`,
                    `" . self::TYPE_PREMIUM_CREDITS_PURCHASED . "`,
                    `" . self::TYPE_TOKEN . "`,
                    `" . self::TYPE_AYAKASHI_FAVOR . "`
                )
                VALUES
                (
                    $user_id,
                    '" . $user_data[self::TYPE_MONEY] . "',
                    '" . $user_data[self::TYPE_PREMIUM_CREDITS] . "',
                    '" . $user_data[self::TYPE_PREMIUM_CREDITS_PURCHASED] . "',
                    0,
                    0
                )
            ");
            return [
                'user_id' => $user_id,
                self::TYPE_MONEY => $user_data[self::TYPE_MONEY],
                self::TYPE_PREMIUM_CREDITS => $user_data[self::TYPE_PREMIUM_CREDITS], 
                self::TYPE_PREMIUM_CREDITS_PURCHASED => $user_data[self::TYPE_PREMIUM_CREDITS_PURCHASED],
                self::TYPE_TOKEN => 0,
                self::TYPE_AYAKASHI_FAVOR => 0
            ];
        }
        return $system->db->fetch($result);
    }
}
