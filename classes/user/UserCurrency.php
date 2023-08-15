<?php
class UserCurrency {
    const USER_TOKENS_ACTIVE = false;
    public function __construct(
        public Currency $money,
        public Currency $premium_credits,
        public Currency $premium_purchased,
        public Currency $tokens
    ) {}

    // Yen functions
    public function addMoney(int $amount, string $description, bool $increment_daily_task = true): void {
        $this->money->add($amount, $description, $increment_daily_task);
    }
    public function subtractMoney(int $amount, string $description): void {
        $this->money->subtract($amount, $description);
    }
    public function getMoney(): int {
        return $this->money->getAmount();
    }
    public function getFormattedMoney(): string {
        return $this->money->getFormattedCurrency();
    }

    // Premium functions
    public function addPremiumCredits(int $amount, string $description, bool $increment_purchased = false): void {
        $this->premium_credits->add($amount, $description);
        if($increment_purchased) {
            $this->addPremiumPurchased($amount, $description);
        }
    }
    public function subtractPremiumCredits(int $amount, string $description, bool $decrement_purchased = false): void {
        $this->premium_credits->subtract($amount, $description);
        if($decrement_purchased) {
            $this->subtractPremiumPurchased($amount, $description);
        }
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
    }
    public function subtractPremiumPurchased(int $amount, string $description, bool $log = false): void {
        $this->premium_purchased->subtract($amount, $description, log: $log);
    }
    public function getPremiumPurchased(): int {
        return $this->premium_purchased->getAmount();
    }
    public function getFormattedPremiumPurchased(): string {
        return $this->premium_purchased->getFormattedCurrency();
    }

    // Token functions
    public function addTokens(int $amount, string $description, bool $log = true): void {
        if(self::USER_TOKENS_ACTIVE) {
            $this->tokens->add($amount, $description, log: $log);
        }
    }
    public function subtractTokens(int $amount, string $description, bool $log = true): void {
        if(self::USER_TOKENS_ACTIVE) {
            $this->tokens->subtract($amount, $description, log: $log);
        }
    }
    public function getTokens(): int {
        return (self::USER_TOKENS_ACTIVE) ? $this->tokens->getAmount() : 0;
    }
    public function getFormattedTokens(): string {
        return (self::USER_TOKENS_ACTIVE) ? $this->tokens->getFormattedCurrency() : "0";
    }
}