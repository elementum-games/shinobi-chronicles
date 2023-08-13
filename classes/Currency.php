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

    public function __construct(
        public System $system,
        public string $type,
        private ?int $user_id,
        private int $amount,

        public ?UserDailyTasks &$userDailyTasks = null,
        public ?int $max_amount = null,
        public array $valid_types = [
            self::TYPE_MONEY, self::TYPE_PREMIUM_CREDITS, self::TYPE_TOKEN
        ]
    ) {
        if(!in_array($this->type, $this->valid_types)) {
            throw new RuntimeException("Invalid currency type!");
        }
    }

    public function add(int $amount, string $description, bool $increment_daily_task = true): void {
        if($increment_daily_task && $this->type == self::TYPE_MONEY && $this->userDailyTasks instanceof UserDailyTasks) {
            $this->userDailyTasks->progressTask(DailyTask::ACTIVITY_EARN_MONEY, $amount);
        }
        if(!is_null($this->max_amount) && $this->amount + $amount > $this->max_amount) {
            $amount = $this->max_amount - $this->amount;
        }
        $this->set($this->amount + $amount, $description);
    }

    public function subtract(int $amount, string $description) {
        if($this->amount - $amount < 0) {
            throw new RuntimeException("Not enough " . System::unSlug($this->type) . "!");
        }
        $this->set($this->amount - $amount, $description);
    }

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
    public function getAmount(): int {
        return $this->amount;
    }
    public function getName(): string {
        return self::getCurrencyName($this->type);
    }
    public function getSymbol(): string {
        return self::getCurrencySymbol($this->type);
    }
    public static function getCurrencyName($type): string {
        return match ($type) {
            self::TYPE_MONEY => self::MONEY_NAME,
            self::TYPE_PREMIUM_CREDITS => self::PREMIUM_NAME,
            self::TYPE_TOKEN => self::TOKEN_NAME,
            default => '???',
        };
    }
    public static function getCurrencySymbol($type): string {
        return match ($type) {
            self::TYPE_MONEY => self::MONEY_SYMBOL,
            self::TYPE_PREMIUM_CREDITS => self::PREMIUM_SYMBOL,
            self::TYPE_TOKEN => self::TOKEN_SYMBOL,
            default => '???',
        };
    }

    public static function getValidCurrencies(): array {
        return [self::TYPE_MONEY, self::TYPE_PREMIUM_CREDITS, self::TYPE_TOKEN];
    }
}