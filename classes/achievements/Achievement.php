<?php

require_once __DIR__ . '/AchievementReward.php';

class Achievement {
    const RANK_LEGENDARY = 4;
    const RANK_ELITE = 3;
    const RANK_GREATER = 2;
    const RANK_COMMON = 1;

    public static array $rank_labels = [
        self::RANK_LEGENDARY => 'Legendary',
        self::RANK_ELITE => 'Elite',
        self::RANK_GREATER => 'Greater',
        self::RANK_COMMON => 'Common',
    ];

    public string $id;
    public int $rank;
    public string $name;
    
    public string $prompt;

    /** @var AchievementReward[] $rewards */
    public array $rewards;

    /*
     * An anonymous function that should take in $system and $player, and return whether the player has met the criteria
     * for this achievement.
     *
     * For example, if your criteria is "Win 100 Arena Battles" your closure could look like this:
     *
     * function(System $system, User $player) {
     *    return $player->ai_wins >= 100;
     * }
     */
    protected Closure $criteria_check_closure;

    public bool $is_world_first;

    /**
     * @param string              $id
     * @param int                 $rank
     * @param string              $name
     * @param string              $prompt
     * @param AchievementReward[] $rewards
     * @param Closure             $criteria_check_closure
     * @param bool                $is_world_first
     */
    public function __construct(
        string $id,
        int $rank,
        string $name,
        string $prompt,
        array $rewards,
        Closure $criteria_check_closure,
        bool $is_world_first = false,
    ) {
        $this->id = $id;
        $this->rank = $rank;
        $this->name = $name;
        $this->prompt = $prompt;
        $this->rewards = $rewards;
        $this->criteria_check_closure = $criteria_check_closure;
        $this->is_world_first = $is_world_first;
    }

    public function isCriteriaAchieved(System $system, User $player) {
        return ($this->criteria_check_closure)($system, $player);
    }

    public function getRankLabel(): string {
        return self::$rank_labels[$this->rank] ?? "None";
    }
}
