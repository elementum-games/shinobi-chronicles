<?php
class UserCurrency {
    const USER_TOKENS_ACTIVE = false;
    public function __construct(
        public System $system,
        public int $user_id,
        public bool $update_data = false,
        public Currency $money,
        public Currency $premium_credits,
        public Currency $premium_purchased,
        public Currency $tokens,
        public Currency $ayakashi_favor
    ) {}

    // Yen functions
    public function addMoney(int $amount, string $description = "", bool $increment_daily_task = true, bool $log = true): void {
        $this->money->add(
            amount: $amount, 
            descripton: $description, 
            increment_daily: $increment_daily_task,
            log: $log
        );
        $this->update_data = true;
    }
    public function subtractMoney(int $amount, string $description = "", bool $log = true): void {
        $this->money->subtract(
            amount: $amount, 
            descriptioin: $description,
            log: $log
        );
        $this->update_data = true;
    }
    public function getMoney(): int {
        return $this->money->getAmount();
    }
    public function getFormattedMoney(): string {
        return $this->money->getFormattedCurrency();
    }
    public function setMoneyDailyTask(UserDailyTasks $daily_tasks): void {
        $this->money->userDailyTasks = $daily_tasks;
    }

    // Premium functions
    public function addPremiumCredits(int $amount, string $description, bool $increment_purchased = false): void {
        $this->premium_credits->add($amount, $description);
        if($increment_purchased) {
            $this->addPremiumPurchased($amount, $description);
        }
        $this->update_data = true;
    }
    public function subtractPremiumCredits(int $amount, string $description, bool $decrement_purchased = false): void {
        $this->premium_credits->subtract($amount, $description);
        if($decrement_purchased) {
            $this->subtractPremiumPurchased($amount, $description);
        }
        $this->update_data = true;
    }
    public function getPremiumCredits(): int {
        return $this->premium_credits->getAmount();
    }
    public function getFormattedPremiumCredits(): string {
        return $this->premium_credits->getFormattedCurrency();
    }

    // Premium purchased functions
    public function addPremiumPurchased(int $amount, string $description, bool $log = false): void {
        $this->premium_purchased->add($amount, $description, log: $log);
        $this->update_data = true;
    }
    public function subtractPremiumPurchased(int $amount, string $description, bool $log = false): void {
        $this->premium_purchased->subtract($amount, $description, log: $log);
        $this->update_data = true;
    }
    public function getPremiumPurchased(): int {
        return $this->premium_purchased->getAmount();
    }
    public function getFormattedPremiumPurchased(): string {
        return $this->premium_purchased->getFormattedCurrency();
    }

    // Favor functions
    public function addFavor(string $favor_type, string $description, $log = true): void {
        $this->$favor_type->add($amount, $description, log: $log);
        $this->update_data = true;
    }
    public function subtractFavor(string $favor_type, string $description, $log = true): void {
        $this->$favor_type->subtract($amount, $description, log: $log);
        $this->update_data = true;
    }
    public function getFactionFavor(string $favor_type): int {
        return $this->$favor_type->getAmount();
    }
    public function getFormattedFactionFavor(string $favor_type) {
        return $this->$favor_type->getFormattedCurrency();
    }

    // Token functions
    public function addTokens(int $amount, string $description, bool $log = true): void {
        if(self::USER_TOKENS_ACTIVE) {
            $this->tokens->add($amount, $description, log: $log);
        }
        $this->update_data = true;
    }
    public function subtractTokens(int $amount, string $description, bool $log = true): void {
        if(self::USER_TOKENS_ACTIVE) {
            $this->tokens->subtract($amount, $description, log: $log);
        }
        $this->update_data = true;
    }
    public function getTokens(): int {
        return (self::USER_TOKENS_ACTIVE) ? $this->tokens->getAmount() : 0;
    }
    public function getFormattedTokens(): string {
        return (self::USER_TOKENS_ACTIVE) ? $this->tokens->getFormattedCurrency() : "0";
    }

    // Global functions
    public function updateCurrencies() {
        if($this->update_data) {
            $this->system->db->query(
                "UPDATE `user_currency` SET 
                    `money`={$this->getMoney()},
                    `premium_credits`={$this->getPremiumCredits()},
                    `premium_credits_purchased`={$this->getPremiumPurchased()},
                    `tokens`={$this->getTokens()},
                    `ayakashi_favor`=" . $this->getFactionFavor(Currency::TYPE_AYAKASHI_FAVOR) . "
                WHERE `user_id`={$this->user_id} LIMIT 1"
            );
        }
    }
}