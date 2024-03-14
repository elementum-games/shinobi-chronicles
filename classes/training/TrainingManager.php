<?php

class TrainingManager {
	/**
     * Training Manager constructor.
     * @param System $system
     * @param User   $player
     * @throws RuntimeException
     */

    public System $system;
    public int $train_time;
    public int $train_gain;
    public string $train_type;
    public ForbiddenSeal $forbidden_seal;
    public ?Team $team;
    public ?Clan $clan;
    public Rank $rank;
    public UserReputation $reputation;
    public ?int $sensei_id;
    public ?int $bloodline_id;
    public VillagePolicy $policy;
    public int $village_training_speed_bonus;

    public int $train_time_remaining;

    const TRAIN_LEN_SHORT = 'short';
    const TRAIN_LEN_LONG = 'long';
    const TRAIN_LEN_EXTENDED = 'extended';
    public static array $valid_train_lengths = [
        self::TRAIN_LEN_SHORT, self::TRAIN_LEN_LONG, self::TRAIN_LEN_EXTENDED
    ];

    public static array $skill_types = [
        'ninjutsu_skill', 'genjutsu_skill', 'taijutsu_skill', 'bloodline_skill'
    ];
    public static array $attribute_types = [
        'speed', 'cast_speed'
    ];
    const BASE_TRAIN_TIME = 600;
    const BASE_STAT_GAIN = 4;
    const LONG_MODIFIER = 2.4;
    const EXTENDED_MODIFIER = 15;

    public int $stat_train_gain;
    public int $stat_long_train_gain;
    public int $stat_extended_train_gain;
    public int $stat_train_length;
    public int $stat_long_train_length;
    public int $stat_extended_train_length;
    public int $base_jutsu_train_length;
    public int $jutsu_train_gain;

    public function __construct(System $system, &$type, &$gain, &$time, $rank, $forbidden_seal, $rep, $team, $clan, $sensei, $bloodline_id, $policy, Village $village) {
        $this->system = $system;

        $this->rank = $rank;
        $this->train_type = &$type;
        $this->train_gain = &$gain;
        $this->train_time = &$time;
        $this->forbidden_seal = $forbidden_seal;
        $this->team = $team;
        $this->clan = $clan;
        $this->reputation = $rep;
        $this->sensei_id = $sensei;
        $this->bloodline_id = $bloodline_id;
        $this->policy = $policy;

        $this->train_time_remaining = $this->train_time - time();

        $this->stat_train_length = self::BASE_TRAIN_TIME;
	    $this->stat_train_gain = self::BASE_STAT_GAIN + ($this->rank->id * 4);

        $this->base_jutsu_train_length = self::BASE_TRAIN_TIME;
	    $this->jutsu_train_gain = User::$jutsu_train_gain;

        // 60% of standard
	    $this->stat_long_train_length = self::BASE_TRAIN_TIME * 4;
	    $this->stat_long_train_gain = $this->stat_train_gain * self::LONG_MODIFIER;

        // 30x length (5 hrs), 15x gains: 50% of standard
        $this->stat_extended_train_length = self::BASE_TRAIN_TIME * 30;
	    $this->stat_extended_train_gain = $this->stat_train_gain * self::EXTENDED_MODIFIER;

	    // Forbidden seal trainings boost
        $this->stat_long_train_length *= $this->forbidden_seal->long_training_time;
        $this->stat_long_train_gain *= $this->forbidden_seal->long_training_gains;

        $this->stat_extended_train_length = round($this->stat_extended_train_length * $this->forbidden_seal->extended_training_time);
        $this->stat_extended_train_gain = round($this->stat_extended_train_gain * $this->forbidden_seal->extended_training_gains);

	    $this->stat_train_gain += $this->system->TRAIN_BOOST;
	    $this->stat_long_train_gain += $this->system->LONG_TRAIN_BOOST;
	    $this->stat_extended_train_gain += ($this->system->LONG_TRAIN_BOOST * System::EXTENDED_BOOST_MULTIPLIER);

        $this->village_training_speed_bonus = $village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_TRAINING_SPEED];
    }

    public function hasActiveTraining() {
        return $this->train_time > 0;
    }

    public function trainType(): string
    {
        if($this->train_time) {
            return ucwords(str_replace(['bloodline_jutsu:', 'jutsu:', '_'], ['', '', ' '], $this->train_type));
        }
        return 'None';
    }

    public function getTrainingAmount($length, $type) {
        if(str_contains($type, 'jutsu:')) {
            $gain = User::$jutsu_train_gain;
            return $gain;
        }
        else {
            $base_gains = $this->stat_train_gain;
            switch($length) {
                case self::TRAIN_LEN_SHORT:
                    $gain = $base_gains;
                    // System boost
                    $gain += $this->system->TRAIN_BOOST;
                    return round($gain);
                case self::TRAIN_LEN_LONG:
                    $gain = $base_gains * self::LONG_MODIFIER;
                    // Forbidden seal
                    $gain *= $this->forbidden_seal->long_training_gains;
                    // Reputation enhancement
                    if($this->reputation->benefits[UserReputation::BENEFIT_EFFICIENT_LONG]){
                        $gain += floor($gain * UserReputation::EFFICIENT_LONG_INCREASE/100);
                    }
                    // System boost
                    $gain += $this->system->LONG_TRAIN_BOOST;
                    return round($gain);
                case self::TRAIN_LEN_EXTENDED:
                    $gain = $base_gains * self::EXTENDED_MODIFIER;
                    // Forbidden seal
                    $gain = round($gain * $this->forbidden_seal->extended_training_gains);
                    // Reputation enhancement
                    if($this->reputation->benefits[UserReputation::BENEFIT_EFFICIENT_EXTENDED]) {
                        $gain += floor($gain * UserReputation::EFFICIENT_EXTENDED_INCREASE/100);
                    }
                    // System boost
                    $gain += ($this->system->LONG_TRAIN_BOOST * System::EXTENDED_BOOST_MULTIPLIER);
                    return round($gain);
                default:
                    return $base_gains;
            }
        }
    }

    public function calcPartialGain($for_display = false) {
        // No partial gains for jutsu
        if(str_contains($this->train_type, 'jutsu:')) {
            return ($for_display) ? "You will no gain any jutsu experience!" : 0;
        }
        else {
            $long_training = ($this->train_gain < $this->getTrainingAmount(self::TRAIN_LEN_EXTENDED, $this->train_type));
            if($long_training) {
                $train_length = $this->getTrainingLength(self::TRAIN_LEN_LONG);
                $completion_rate = round(($train_length - $this->train_time_remaining) / $train_length, 2);
            }
            else {
                $train_length = $this->getTrainingLength(self::TRAIN_LEN_EXTENDED);
                $completion_rate = round(($train_length - $this->train_time_remaining) / $train_length, 2);
            }

            // Less than 50% completion
            if($completion_rate < 0.5) {
                $time_elapsed = $train_length - $this->train_time_remaining;

                // Less than 10 minutes
                if($time_elapsed < self::BASE_TRAIN_TIME) {
                    return ($for_display) ? "You will not gain any of your potential {$this->train_gain} " . $this->trainType() . " points."
                        : 0;
                }
                // 10 minutes or more
                else {
                    // Check extended training for long training rate
                    if(!$long_training) {
                        if($time_elapsed >= $this->getTrainingLength(self::TRAIN_LEN_LONG)) {
                            return ($for_display) ? "You will gain " . $this->getTrainingAmount(self::TRAIN_LEN_LONG, $this->train_type) .
                                " " . $this->trainType() . " points." : $this->getTrainingAmount(self::TRAIN_LEN_LONG, $this->train_type);
                        }
                    }
                    // Return short training gain
                    return ($for_display) ? "You will gain " . $this->getTrainingAmount(self::TRAIN_LEN_SHORT, $this->train_type) .
                        " " . $this->trainType() . " points." : $this->getTrainingAmount(self::TRAIN_LEN_SHORT, $this->train_type);
                }
            }
            else {
                // Long trainings base of 40%, extended base of 30%
                $to_gain = ($long_training) ? 0.4 : 0.3;

                // Calculate additional gains (up to an additional 31%)
                $to_gain += round(($completion_rate-0.5) / 1.5, 2);

                // Set total amount
                $to_gain = floor($this->train_gain * $to_gain);

                return ($for_display) ? "You will gain $to_gain " . $this->trainType() . " skill points"
                    : $to_gain;
            }
        }
    }

    public function getTrainingLength($length, $jutsu = false, $in_mins = false) {
        if($jutsu != false) {
            // Clan boost info
            $clan_boost = false;
            if (isset($this->clan)) {
                if ($this->clan->boost_effect == 'jutsu') {
                    $clan_boost = $this->clan->boost_amount;
                }
            }
            // True time calculation for setting training
            if($jutsu instanceof Jutsu) {
                $len = self::BASE_TRAIN_TIME + (60 * round(pow($jutsu->level, 1.1)));
                // Clan boost
                if ($clan_boost) {
                    $len *= 1 - ($clan_boost / 100);
                }
                // Reputation boost
                if ($this->reputation->benefits[UserReputation::BENEFIT_JUTSU_TRAINING_BONUS]) {
                    $len = round($len * (100 / (100 + UserReputation::JUTSU_TRAINING_BONUS)));
                }
                // Policy boost
                if ($this->policy->training_speed > 0) {
                    $len = round($len * (100 / (100 + $this->policy->training_speed)));
                }
                // Village boost
                if ($this->village_training_speed_bonus > 0) {
                    $len = round($len * (100 / (100 + $this->village_training_speed_bonus)));
                }
                return ($in_mins) ? $len/60 : $len;
            }
            // Used for basic display in training menu
            return ($in_mins) ? self::BASE_TRAIN_TIME / 60 : self::BASE_TRAIN_TIME;
        }
        else {
            // Sensei boost info
            $sensei_boost = false;
            if($this->sensei_id) {
                $sensei_boost = SenseiManager::getTrainingBoostForTrainType($this->sensei_id, $this->train_type, $this->bloodline_id, $this->system);
            }
            // Clan boost info
            $clan_boost = false;
            if (isset($this->clan) && $this->clan->boost_type == 'training' && $this->clan->boost_effect == $this->train_type) {
                $clan_boost = $this->clan->boost_amount;
            }
            // Length logic
            switch($length) {
                case self::TRAIN_LEN_SHORT:
                    $train_length = self::BASE_TRAIN_TIME;
                    // Sensei boost
                    if($sensei_boost) {
                        $train_length *= 1 - ($sensei_boost/100);
                    }
                    // Clan boost
                    if ($clan_boost) {
                        $train_length *= 1 - ($clan_boost / 100);
                    }
                    // Policy boost
                    if ($this->policy->training_speed > 0) {
                        $train_length = round($train_length * (100 / (100 + $this->policy->training_speed)));
                    }
                    // Village boost
                    if ($this->village_training_speed_bonus > 0) {
                        $train_length = round($train_length * (100 / (100 + $this->village_training_speed_bonus)));
                    }
                    return ($in_mins) ? self::formatSecondsToMinutes($train_length) : $train_length;
                case self::TRAIN_LEN_LONG:
                    $train_length = self::BASE_TRAIN_TIME * 4;
                    // Forbidden seal augment
                    $train_length *= $this->forbidden_seal->long_training_time;
                    // Sensei boost
                    if($sensei_boost) {
                        $train_length *= 1 - ($sensei_boost/100);
                    }
                    // Clan boost
                    if ($clan_boost) {
                        $train_length *= 1 - ($clan_boost / 100);
                    }
                    // Policy boost
                    if ($this->policy->training_speed > 0) {
                        $train_length = round($train_length * (100 / (100 + $this->policy->training_speed)));
                    }
                    // Village boost
                    if ($this->village_training_speed_bonus > 0) {
                        $train_length = round($train_length * (100 / (100 + $this->village_training_speed_bonus)));
                    }
                    return ($in_mins) ? self::formatSecondsToMinutes($train_length) : $train_length;
                case self::TRAIN_LEN_EXTENDED:
                    $train_length = self::BASE_TRAIN_TIME * 30;
                    // Forbidden seal augment
                    $train_length = round($train_length * $this->forbidden_seal->extended_training_time);
                    // Sensei boost
                    if($sensei_boost) {
                        $train_length *= 1 - ($sensei_boost/100);
                    }
                    // Clan boost
                    if ($clan_boost) {
                        $train_length *= 1 - ($clan_boost / 100);
                    }
                    // Policy boost
                    if ($this->policy->training_speed > 0) {
                        $train_length = round($train_length * (100 / (100 + $this->policy->training_speed)));
                    }
                    // Village boost
                    if ($this->village_training_speed_bonus > 0) {
                        $train_length = round($train_length * (100 / (100 + $this->village_training_speed_bonus)));
                    }
                    return ($in_mins) ? self::formatSecondsToMinutes($train_length) : $train_length;
                default:
                    return ($in_mins) ? self::formatSecondsToMinutes(self::BASE_TRAIN_TIME) : self::BASE_TRAIN_TIME;
            }
        }
    }

    public static function formatSecondsToMinutes($seconds) {
        $minutes = floor($seconds/60);
        $seconds %= $minutes * 60;
        $string = $minutes . ' minute' . (($minutes > 1) ? 's' : '');
        if($seconds > 0) {
            $string .= ' ' . $seconds . ' second' . (($seconds > 1) ? 's' : '');
        }
        return $string;
    }

    public function getTrainingInfo($length, $type) {
        if(str_contains($type, "jutsu:")) {
            $gain = $this->getTrainingAmount($length, $type);

            return "Takes " . $this->getTrainingLength($length, true, true) . " or more minutes depending on level, "
                . "increases level by $gain";
        }
        else {
            switch ($length) {
                case self::TRAIN_LEN_SHORT:
                case self::TRAIN_LEN_LONG:
                case self::TRAIN_LEN_EXTENDED:
                    $gain = $this->getTrainingAmount($length, $type);
                    return "Takes " . $this->getTrainingLength($length, false, true) . ", gives "
                        . $gain . " point" . ($gain > 1 ? 's' : '');
                default:
                    return 'Invalid training type!';
            }
        }
    }

    public function trainingDisplay() {
        $train_gain = $this->train_gain;
        if (!empty($this->system->event) && $this->system->event->exp_gain_multiplier > 1) {
            $train_gain *= $this->system->event->exp_gain_multiplier;
        }
        if(str_contains($this->train_type, 'jutsu:')) {
            return "You will gain " . User::$jutsu_train_gain . " jutsu levels once training is complete!";
        }
        else {
            $display = "You will gain {$train_gain} {$this->trainType()} skill point";
            if($train_gain > 1) {
                $display .= "s";
            }
            $display .= " once you have completed training.";

            return $display;
        }
    }

    public function setTraining($type, $length, $amount) {
        $this->train_type = $type;
        $this->train_time = time() + $length;
        $this->train_time_remaining = $length;
        $this->train_gain = $amount;
    }

    /**
     * @param string $difficulty_level
     * @param int $rank
     * @return int
     */
    public static function getAIStatGain(string $difficulty_level, int $rank): int
    {
        switch ($difficulty_level) {
            case NPC::DIFFICULTY_NONE:
                return 1;
            case NPC::DIFFICULTY_EASY:
                return 1;
            case NPC::DIFFICULTY_NORMAL:
                switch ($rank) {
                    case 1:
                    case 2:
                        return 2;
                    case 3:
                    case 4:
                        return 3;
                    case 5:
                        return 4;
                }
                return 2;
            case NPC::DIFFICULTY_HARD:
                switch ($rank) {
                    case 1:
                    case 2:
                        return 4;
                    case 3:
                    case 4:
                        return 6;
                    case 5:
                        return 8;
                }
                return 4;
            default:
                return 1;
        }
    }
}
