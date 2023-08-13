<?php
class Currency {
    const TYPE_MONEY = 'money';
    const TYPE_PREMIUM_CREDITS = 'premium_credits';
    const TYPE_TOKEN = 'tokens';

    const MONEY_NAME = "Yen";
    const PREMIUM_NAME = "Ancient Kunai";
    const TOKEN_NAME = "Ancient Shurkien";

    const MONEY_SYMBOL = "&yen;";
    const PREMIUM_SYMBOL = "AK";
    const TOKEN_SYMBOL = "AS";

    public array $valid_currency_types;
    public string $name;
    public string $symbol;

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
            throw new RunetimeException("Invalid currency type {$this->type}!");
        }
            
        $this->name = self::getCurrencyName($this->type);
        $this->symbol = self::getCurrencySymbol($this->type);
    }

    // Process currency increase, set amount and log currency
    public function add(int $amount, string $description, bool $increment_daily_task = true): void {
        if($increment_daily_task && $this->type == self::TYPE_MONEY && $this->userDailyTasks instanceof UserDailyTasks) {
            $this->userDailyTasks->progressTask(DailyTask::ACTIVITY_EARN_MONEY, $amount);
        }
        if(!is_null($this->max_amount) && $this->amount + $amount > $this->max_amount) {
            $amount = $this->max_amount - $this->amount;
        }
        $this->set($this->amount + $amount, $description);
    }

    // Process currency reduction, set amount and log transaction
    public function subtract(int $amount, string $description) {
        if($this->amount - $amount < 0) {
            throw new RuntimeException("Not enough " . System::unSlug($this->type) . "!");
        }
        $this->set($this->amount - $amount, $description);
    }

    // Set currency to new amount and log transaction
    public function set(int $new_amount, string $description): void {
        $this->system->currencyLog(
            character_id: $this->user_id,
            currency_type: $this->type,
            previous_balance: $this->amount,
            new_balance: $new_amount,
            transaction_amount: $new_amount - $this->amount,
            transaction_description: $description
        );
        $this->amount = $new_amount;
    }

    // Return currnt amount of currency
    public function getAmount(): int {
        return $this->amount;
    }

    /** Currency Conventions **/
    public static function getValidCurrencies(): array {
        return [self::TYPE_MONEY, self::TYPE_PREMIUM_CREDITS, /*self::TYPE_TOKEN*/];
    }
    
    public function getName(): string {
        return self::getCurrencyName($this->type);
    }
    
    public function getSymbol(): string {
        return self::getCurrencySymbol($this->type);
    }
    
    public static function getCurrencyName(string $type): string {
        return match ($type) {
            self::TYPE_MONEY => self::MONEY_NAME,
            self::TYPE_PREMIUM_CREDITS => self::PREMIUM_NAME,
            self::TYPE_TOKEN => self::TOKEN_NAME,
            default => '???',
        };
    }
    
    public static function getCurrencySymbol(string $type): string {
        return match ($type) {
            self::TYPE_MONEY => self::MONEY_SYMBOL,
            self::TYPE_PREMIUM_CREDITS => self::PREMIUM_SYMBOL,
            self::TYPE_TOKEN => self::TOKEN_SYMBOL,
            default => '???',
        };
    }

    /** Calculate Yen Gains **/
    public static function calcRawYenGain(int $rank_num, int $multiplier): int {
        $gain = ceil(((30 * $rank_num) + pow($rank_num+1, 2)) * $multiplier);
        return $gain;
    }

    public static function roundYen(int $num, int $multiple_of) {
        $remainder = $num % $multiple_of;
        if($num / $multiple_of >= 0.5) {
            return $num + ($multiple_of - $remainder);
        }
        return $num - $remainder;
    }

    public static funcion getRoundedYen(int $rank_num, int $multiplier, int $multiple_of) {
        return self::roundYen(
            num: self::calcRawYenGain($rank_num, $multiplier),
            multiple_of: $multiple_of
        );
    }
}
