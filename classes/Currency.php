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

    const MAX_TOKENS = 0;

    public array $valid_currency_types;
    public string $name;
    public string $symbol;

    // Faction Currencies
    const TYPE_AYAKASHI_FAVOR = "ayakashi_favor";

    const AYAKASHI_NAME = "Ayakashi's Favor";

    const AYAKASHI_SYMBOL = "AF";

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
            throw new RuntimeException("Invalid currency type {$this->type}!");
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
    public function subtract(int $amount, string $description, bool $log = true) {
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
        return ceil(((30 * $rank_num) + pow($rank_num+1, 2)) * $multiplier);
    }

    public static function roundYen(int $num, int $multiple_of): int {
        return $num * round($num / $multiple_of);
    }

    public static function getRoundedYen(int $rank_num, int $multiplier, int $multiple_of): int {
        return self::roundYen(
            num: self::calcRawYenGain($rank_num, $multiplier),
            multiple_of: $multiple_of
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
