<?php

require_once __DIR__ . '/Event.php';

class LanternEvent extends Event {
    public array $mission_coords = [];

    public array $item_ids = [];
    public array $mission_ids = [];

    public array $config = [];

    public function __construct(DateTimeImmutable $end_time) {
        $this->name = "Lantern";
        $this->end_time = $end_time;

        // Manually set event locations, pulled from TravelManager and Missions to identify event missions
        $currentMinutes = System::currentMinute();
        $mission_coords_gold = [];
        $mission_coords_special = [];
        $mission_coords_easy = [];
        $mission_coords_medium = [];
        $mission_coords_hard = [];
        $mission_coords_nightmare = [];
        
        switch (true) {
            case ($currentMinutes < 300): // 3 minutes per hour
                $mission_coords_nightmare[] = ['x' => 10, 'y' => 1];
                $mission_coords_special[] = ['x' => 13, 'y' => 1];
                $mission_coords_special[] = ['x' => 12, 'y' => 3];
                $mission_coords_special[] = ['x' => 8, 'y' => 3];
                $mission_coords_special[] = ['x' => 7, 'y' => 1];
                break;
            case ($currentMinutes % 3 == 0):
                $mission_coords_easy[] = ['x' => 5, 'y' => 4];
                $mission_coords_easy[] = ['x' => 2, 'y' => 11];
                $mission_coords_easy[] = ['x' => 17, 'y' => 10];
                $mission_coords_easy[] = ['x' => 23, 'y' => 2];
                $mission_coords_easy[] = ['x' => 22, 'y' => 16];
                $mission_coords_easy[] = ['x' => 26, 'y' => 14];
                $mission_coords_easy[] = ['x' => 27, 'y' => 4];
                $mission_coords_easy[] = ['x' => 13, 'y' => 8];
                $mission_coords_easy[] = ['x' => 6, 'y' => 7];
                $mission_coords_easy[] = ['x' => 5, 'y' => 14];
                $mission_coords_hard[] = ['x' => 15, 'y' => 3];
                $mission_coords_hard[] = ['x' => 24, 'y' => 9];
                $mission_coords_hard[] = ['x' => 14, 'y' => 14];
                $mission_coords_hard[] = ['x' => 8, 'y' => 10];
                break;
            case ($currentMinutes % 3 == 1):
                $mission_coords_easy[] = ['x' => 5, 'y' => 4];
                $mission_coords_easy[] = ['x' => 2, 'y' => 11];
                $mission_coords_easy[] = ['x' => 17, 'y' => 10];
                $mission_coords_easy[] = ['x' => 23, 'y' => 2];
                $mission_coords_easy[] = ['x' => 22, 'y' => 16];
                $mission_coords_easy[] = ['x' => 26, 'y' => 14];
                $mission_coords_easy[] = ['x' => 27, 'y' => 4];
                $mission_coords_easy[] = ['x' => 13, 'y' => 8];
                $mission_coords_easy[] = ['x' => 6, 'y' => 7];
                $mission_coords_easy[] = ['x' => 5, 'y' => 14];
                $mission_coords_medium[] = ['x' => 9, 'y' => 5];
                $mission_coords_medium[] = ['x' => 3, 'y' => 14];
                $mission_coords_medium[] = ['x' => 15, 'y' => 11];
                $mission_coords_medium[] = ['x' => 24, 'y' => 5];
                $mission_coords_medium[] = ['x' => 24, 'y' => 17];
                break;
            default:
                $mission_coords_easy[] = ['x' => 7, 'y' => 3];
                $mission_coords_easy[] = ['x' => 5, 'y' => 7];
                $mission_coords_easy[] = ['x' => 2, 'y' => 13];
                $mission_coords_easy[] = ['x' => 4, 'y' => 14];
                $mission_coords_easy[] = ['x' => 13, 'y' => 10];
                $mission_coords_easy[] = ['x' => 17, 'y' => 7];
                $mission_coords_easy[] = ['x' => 25, 'y' => 1];
                $mission_coords_easy[] = ['x' => 26, 'y' => 5];
                $mission_coords_easy[] = ['x' => 26, 'y' => 16];
                $mission_coords_easy[] = ['x' => 22, 'y' => 14];
                break;
        }

        $minute_seed = floor(time() / 60);
        mt_srand($minute_seed);
        
        if (mt_rand(0, 9) == 0) {
            $mission_coords_gold[] = ['x' => mt_rand(1, 28), 'y' => mt_rand(1, 18)];
        }
        // clear seed
        mt_srand();

        $this->mission_coords['gold'] = $mission_coords_gold;
        $this->mission_coords['special'] = $mission_coords_special;
        $this->mission_coords['easy'] = $mission_coords_easy;
        $this->mission_coords['medium'] = $mission_coords_medium;
        $this->mission_coords['hard'] = $mission_coords_hard;
        $this->mission_coords['nightmare'] = $mission_coords_nightmare;

        $this->item_ids['red_lantern_id'] = 119;
        $this->item_ids['blue_lantern_id'] = 120;
        $this->item_ids['violet_lantern_id'] = 121;
        $this->item_ids['gold_lantern_id'] = 129;
        $this->item_ids['shadow_essence_id'] = 123;
        $this->item_ids['sacred_lantern_red_id'] = 124;
        $this->item_ids['sacred_lantern_blue_id'] = 125;
        $this->item_ids['sacred_lantern_violet_id'] = 126;
        $this->item_ids['sacred_lantern_gold_id'] = 130;
        $this->item_ids['forbidden_jutsu_scroll_id'] = 127;

        $this->mission_ids['gold_mission_id'] = 120;
        $this->mission_ids['special_mission_id'] = 119;
        $this->mission_ids['easy_mission_id'] = 112;
        $this->mission_ids['medium_mission_id'] = 113;
        $this->mission_ids['hard_mission_id'] = 111;
        $this->mission_ids['nightmare_mission_id'] = 114;

        $this->config['yen_per_lantern'] = 25;
    }
}
