<?php

class TrainingManager {
	/**
     * Training Manager constructor.
     * @param System $system
     * @param User   $player
     * @throws RuntimeException
     */

    public System $system;

    public User $player;

    public int $stat_train_gain;
    public int $stat_long_train_gain;
    public int $stat_extended_train_gain;
    public int $stat_train_length;
    public int $stat_long_train_length;
    public int $stat_extended_train_length;
    public int $jutsu_train_gain;
    
    public function __construct(System $system, User $player) {
        $this->system = $system;
        $this->player = $player;

        $this->stat_train_length = 600;
	    $this->stat_train_gain = 4 + ($player->rank_num * 4);

	    $this->jutsu_train_gain = User::$jutsu_train_gain;

	    // 56.25% of standard
	    $this->stat_long_train_length = $this->stat_train_length * 4;
	    $this->stat_long_train_gain = $this->stat_train_gain * 2.25;

        // 30x length (5 hrs), 12x gains: 40% of standard
        $this->stat_extended_train_length = $this->stat_train_length * 30;
	    $this->stat_extended_train_gain = $this->stat_train_gain * 12;

	    // Forbidden seal trainings boost
        $this->stat_long_train_length *= $this->player->forbidden_seal->long_training_time;
        $this->stat_long_train_gain *= $this->player->forbidden_seal->long_training_gains;

        $this->stat_extended_train_length = round($this->stat_extended_train_length * $this->player->forbidden_seal->extended_training_time);
        $this->stat_extended_train_gain = round($this->stat_extended_train_gain * $this->player->forbidden_seal->extended_training_gains);

	    $this->stat_train_gain += $this->system->TRAIN_BOOST;
	    $this->stat_long_train_gain += $this->system->LONG_TRAIN_BOOST;
	    $this->stat_extended_train_gain += ($this->system->LONG_TRAIN_BOOST * 5);
    }
}