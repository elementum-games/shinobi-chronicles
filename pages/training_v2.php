<?php

require_once __DIR__ . '/../classes/notification/NotificationManager.php';
require_once __DIR__ . '/../classes/training/TrainingManager.php';

function training() {
    global $system;
    global $player;
    global $self_link;

    // Load player data
    $player->loadTrainingManager();
    $player->getInventory();
    // Set valid skills
    $valid_skills = TrainingManager::$skill_types;
    $trainable_bl_jutsu = false;
    // Remove BL skill from trainable stats
    if($player->bloodline_id == 0) {
        unset($valid_skills[array_search('bloodline_skill', $valid_skills)]);
    }
    // Check for trainable bl jutsu
    else {
        if(!empty($player->bloodline->jutsu)) {
            foreach($player->bloodline->jutsu as $jutsu) {
                if($jutsu->level < Jutsu::MAX_LEVEL) {
                    $trainable_bl_jutsu = true;
                }
            }
        }
    }
    // Set valid attributes
    $valid_attributes = TrainingManager::$attribute_types;

    if(!empty($_POST['train_type']) && !$player->trainingManager->train_time) {
        try {
            $train_length_period = strtolower($_POST['train_type']);
            $train_skill = isset($_POST['skill']);
            $train_attrib = isset($_POST['attributes']);
            $train_jutsu = isset($_POST['jutsu']);
            $train_bl_jutsu = isset($_POST['bloodline_jutsu']);

            // Validate train type
            if(!in_array($train_length_period, TrainingManager::$valid_train_lengths) && $train_length_period != 'train') {
                throw new RunTimeException("Invalid training type!");
            }
            // Check if pvp is active at current location
            if($player->rank_num > 2 && $player->current_location->location_id && !$player->current_location->pvp_allowed) {
                throw new RuntimeException("You cannot train at this location!");
            }

            // Skill & attribute training
            if($train_skill || $train_attrib) {
                $stat = ($train_skill) ? $system->db->clean($_POST['skill']) : $system->db->clean($_POST['attributes']);
                //Validate stat
                if($train_skill && !in_array($stat, $valid_skills)) {
                    throw new RuntimeException("Invalid skill!");
                }
                if($train_attrib && !in_array($stat, $valid_attributes)) {
                    throw new RuntimeException("Invalid attribute!");
                }

                $train_amount = $player->trainingManager->getTrainingAmount($train_length_period, $train_skill);
                $train_length = $player->trainingManager->getTrainingLength($train_length_period);

                //Set training
                $player->trainingManager->setTraining($stat, $train_length, $train_amount);
                $system->message(System::unSlug($stat) . " training started!");

                // Training notification
                $new_notification = new NotificationDto(
                    type: "training",
                    message: "Training {$player->trainingManager->trainType()}",
                    user_id: $player->user_id,
                    created: time(),
                    duration: $train_length,
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
            }
            elseif($train_jutsu || $train_bl_jutsu) {
                $training_prepend = 'jutsu:';
                $jutsu_id = ($train_jutsu) ? (int) $_POST['jutsu'] : (int) $_POST['bloodline_jutsu'];

                //Validate if user has jutsu
                if($train_jutsu) {
                    if(!$player->hasJutsu($jutsu_id)) {
                        throw new RuntimeException("Invalid jutsu!");
                    }
                    $jutsu = $player->jutsu[$jutsu_id];
                }
                if($train_bl_jutsu) {
                    $training_prepend = 'bloodline_jutsu:';
                    if(!isset($player->bloodline)) {
                        throw new RuntimeException("You do not have a bloodline!");
                    }
                    if(!isset($player->bloodline->jutsu[$jutsu_id])) {
                        throw new RuntimeException("You do not have this bloodline jutsu!");
                    }
                    $jutsu = $player->bloodline->jutsu[$jutsu_id];
                }
                // Validate rank requirement
                if($player->rank_num < $jutsu->rank) {
                    throw new RuntimeException("Invalid user rank!");
                }
                // Validate level constraint
                if($jutsu->level >= Jutsu::MAX_LEVEL) {
                    throw new RuntimeException("You can not train {$jutsu->name} any further!");
                }

                $train_type = $training_prepend . System::slug($jutsu->name);
                $train_gain = $jutsu_id;
                $train_length = $player->trainingManager->getTrainingLength(TrainingManager::BASE_TRAIN_TIME, $jutsu);
                $player->trainingManager->setTraining($train_type, $train_length, $train_gain);
                $system->message("Started training {$jutsu->name}!");

                // Set notification
                $new_notification = new NotificationDto(
                    type: "training",
                    message: "Training " . System::unSlug($jutsu->name),
                    user_id: $player->user_id,
                    created: time(),
                    duration: $train_length,
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
            }
            else {
                throw new RuntimeException("Invalid training type_2!");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    if($player->trainingManager->hasActiveTraining() && isset($_GET['cancel_training']) && isset($_GET['cancel_confirm'])) {
        $partial_gain = $player->trainingManager->calcPartialGain();
        if($partial_gain > 0) {
            // Redundancy to bypass jutsu
            if(!str_contains($player->trainingManager->train_type, 'jutsu:')) {
                // Add partial gains
                $stat = $player->trainingManager->train_type;
                $formatted_name = $player->trainingManager->trainType();
                $player->train_time = 0;
                $player->$stat += $partial_gain;
                $player->updateTotalStats();
                $player->exp = $player->total_stats * 10;
                $player->updateData();
                // Create notification
                $new_notification = new NotificationDto(
                    type: "training_complete",
                    message: "You have cancelled your $formatted_name training and gained $partial_gain points and "
                        . $partial_gain * 10 . " experience",
                    user_id: $player->user_id,
                    created: time(),
                    alert: true,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_UNIQUE);
            }
        }
        else {
            $system->message("Training cancelled!");
        }
    }
    elseif($player->trainingManager->hasActiveTraining() && isset($_GET['cancel_training']) && $player->reputation->benefits[UserReputation::BENEFIT_PARTIAL_TRAINING_GAINS]) {
        $partial_gain = $player->trainingManager->calcPartialGain(true);
    }

    $system->printMessage();
    require 'templates/training.php';
}