<?php

require_once __DIR__ . '/../classes/notification/NotificationManager.php';
require_once __DIR__ . '/../classes/training/TrainingManager.php';

function academy() {
	global $system;

	global $player;

	global $self_link;

	global $RANK_NAMES;

	// If exam submitted
	if (isset($_POST['submit_exam'])) {
		try {
			// check if already sensei
			if (SenseiManager::isActiveSensei($player->sensei_id, $system)) {
                throw new RuntimeException('You do not meet the requirements!');
            }
			// check rank
            if ($player->rank_num < 4) {
                throw new RuntimeException('You do not meet the requirements!');
            }
			// check level
            if ($player->level < 75) {
                throw new RuntimeException('You do not meet the requirements!');
            }
			// check justu mastered
            $mastered_count = 0;
            $player->getInventory();
            foreach ($player->jutsu as $jutsu) {
                if ($jutsu->level == 100) {
                    $mastered_count++;
                }
            }
            if ($mastered_count < 5) {
                throw new RuntimeException('You do not meet the requirements!');
            }
			$answers = [$_POST['question1'], $_POST['question2'], $_POST['question3'], $_POST['question4'], $_POST['question5'], $_POST['question6']];
			if (SenseiManager::scoreExam($answers, $system)) {
				$success = SenseiManager::addSensei($player->user_id, $_POST['specialization'], $system);
				if (!$success) {
                    throw new RuntimeException('Something went wrong!');
                }
				$system->message("You passed!");
			}
			else {
                throw new RuntimeException('Check your answers and try again!');
            }
        }
		catch (RuntimeException $e) {
			$system->message($e->getMessage());
        }
    }
	// If resignation confirmed
	if (isset($_POST['confirm_resignation'])) {
		try {
			// check if sensei
			if (!SenseiManager::isActiveSensei($player->user_id, $system)) {
                throw new RuntimeException('You are not a sensei!');
            }
            $success = SenseiManager::removeSensei($player->user_id, $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$system->message("You have resigned as Sensei!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If resign clicked, set flag
	$resign = false;
	if (isset($_GET['resign'])) {
        if (SenseiManager::isActiveSensei($player->user_id, $system)) {
            $resign = true;
        }
    }
	// If kick student
	if (isset($_GET['kick'])) {
		try {
			$success = SenseiManager::removeStudent($player->user_id, (int)$_GET['kick'], $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$system->message("You have kicked your student!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If leave sensei
	if (isset($_GET['leave'])) {
		try {
			$success = SenseiManager::removeStudent($player->sensei_id, $player->user_id, $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$player->sensei_id = 0;
			$system->message("You have left your Sensei!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If create application
	if (isset($_GET['apply'])) {
        try {
			$sensei = User::loadFromId($system, (int)$_GET['apply'], true);
			// check if already student
			if ($player->sensei_id != 0) {
                throw new RuntimeException('You already have a sensei!');
            }
			// check eligibility
			if ($player->rank_num > 2)
            {
                throw new RuntimeException('You are not eligible to become a student!');
            }
			// check is sensei
			if (!SenseiManager::isActiveSensei($sensei->user_id, $system)) {
                throw new RuntimeException('Player is not a valid sensei!');
            }
			// check village
			if ($sensei->village->name != $player->village->name) {
                throw new RuntimeException('Player is not a valid sensei!');
            }
			// check if accepting students
			if (!$sensei->accept_students) {
                throw new RuntimeException('Player is not accepting students!');
            }
			// check if slot available
			if (!SenseiManager::hasSlot($sensei->user_id, $system)) {
				throw new RuntimeException('No student slots available!');
			}
			$success = SenseiManager::createApplication((int)$_GET['apply'], $player->user_id, $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$system->message("You have submitted an application!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If cancel application
	if (isset($_GET['close'])) {
        try {
			$success = SenseiManager::closeApplication((int)$_GET['close'], $player->user_id, $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$system->message("You have closed an application!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If accept application
	if (isset($_GET['accept'])) {
        try {
			$success = SenseiManager::acceptApplication($player->user_id, (int)$_GET['accept'], $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$system->message("You have accepted an application!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If deny application
	if (isset($_GET['deny'])) {
        try {
			$success = SenseiManager::closeApplication($player->user_id, (int)$_GET['deny'], $system);
			if (!$success) {
                throw new RuntimeException('Something went wrong!');
            }
			$system->message("You have denied an application!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If mod clear message
	if (isset($_GET['clear'])) {
        try {
			if (!$player->staff_manager->isModerator()) {
                throw new RuntimeException('Not a moderator!');
            }
			$success = SenseiManager::updateStudentRecruitment((int)$_GET['clear'], '', $system);
			$system->message("You have removed a recruitment message!");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    $lesson = false;
    if (isset($_POST['lesson'])) {
        try {
            $lesson_data = [];
            $lesson_data['sensei_name'] = $system->db->clean($_POST['sensei_name']);
            $lesson_data['stat'] = $system->db->clean($_POST['lesson']);
            $lesson_data['train_type'] = $system->db->clean($_POST['train_type']);
            $lesson_data['sensei_id'] = $system->db->clean($_POST['sensei_id']);
            $lesson_duration = SenseiManager::getLessonDurationForPlayer($player, $system);
            $lesson_cost = SenseiManager::getLessonCostForPlayer($player, $system);
            switch ($_POST['train_type']) {
                case 'short':
                    $lesson_data['duration'] = $lesson_duration['short'] / 60;
                    $lesson_data['cost'] = $lesson_cost['short'];
                    break;
                case 'long':
                    $lesson_data['duration'] = $lesson_duration['long'] / 60;
                    $lesson_data['cost'] = $lesson_cost['long'];
                    break;
                case 'extended':
                    $lesson_data['duration'] = $lesson_duration['extended'] / 60;
                    $lesson_data['cost'] = $lesson_cost['extended'];
                    break;
                default:
                    throw new RuntimeException('Invalid train duration!');
            }
            $lesson = true;
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    if (isset($_POST['confirm_lesson'])) {
        try {
            $lesson_data = [];
            $lesson_data['sensei_id'] = $system->db->clean($_POST['sensei_id']);
            $lesson_data['lesson_stat'] = $system->db->clean($_POST['lesson_stat']);
            $lesson_data['train_type'] = $system->db->clean($_POST['train_type']);
            $lesson_sensei = SenseiManager::getSenseiByID($lesson_data['sensei_id'], $system);

			// If minimum rank
            if ($player->rank_num < 3) {
                throw new RuntimeException('Insufficient rank!');
            }

			// If not training
            if ($player->train_time > 0) {
                throw new RuntimeException('Training already in progress!');
            }

			// If sensei
            if (!isset($lesson_sensei)) {
                throw new RuntimeException('Target player is not a valid sensei!');
            }

			// If active
			if (!SenseiManager::isActiveSensei($lesson_data['sensei_id'], $system)) {
				throw new RuntimeException('Target player is not a valid sensei!');
            }
            $lesson_sensei += SenseiManager::getSenseiUserData($lesson_data['sensei_id'], $system);

			// If in village
			if ($player->village->name != $lesson_sensei['village']) {
				throw new RuntimeException('Target player is not a valid sensei!');
            }

			// If accepting lessons
            if (!((bool) $lesson_sensei['enable_lessons'] && (bool) $lesson_sensei['accept_students'])) {
                throw new RuntimeException('Target player is not a valid sensei!');
            }

			// If has slot available
            if (!SenseiManager::hasSlot($lesson_data['sensei_id'], $system)) {
                throw new RuntimeException('Target player is not a valid sensei!');
            }
			$lesson_cost = SenseiManager::getLessonCostForPlayer($player, $system)[$lesson_data['train_type']];
			$lesson_duration = SenseiManager::getLessonDurationForPlayer($player, $system)[$lesson_data['train_type']];
            switch ($_POST['lesson_stat']) {
                case 'ninjutsu_skill':
                    $lesson_modifier = SenseiManager::getLessonModifier($player->ninjutsu_skill, $player->exp, $lesson_sensei['ninjutsu_skill'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'ninjutsu_skill' ? true : false);
                    break;
                case 'taijutsu_skill':
                    $lesson_modifier = SenseiManager::getLessonModifier($player->taijutsu_skill, $player->exp, $lesson_sensei['taijutsu_skill'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'taijutsu_skill' ? true : false);
                    break;
                case 'genjutsu_skill':
                    $lesson_modifier = SenseiManager::getLessonModifier($player->genjutsu_skill, $player->exp, $lesson_sensei['genjutsu_skill'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'genjutsu_skill' ? true : false);
                    break;
                case 'bloodline_skill':
					if ($player->bloodline_id == $lesson_sensei['bloodline_id']) {
						$lesson_modifier = round(SenseiManager::getLessonModifier($player->bloodline_skill, $player->exp, $lesson_sensei['bloodline_skill'], $lesson_sensei['exp'], true) * 100 - 100, 2);
					} else {
						throw new RuntimeException('Invalid stat!');
                    }
                    break;
                case 'speed':
                    $lesson_modifier = SenseiManager::getLessonModifier($player->speed, $player->exp, $lesson_sensei['speed'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'speed' ? true : false);
                    break;
                case 'cast_speed':
                    $lesson_modifier = SenseiManager::getLessonModifier($player->cast_speed, $player->exp, $lesson_sensei['cast_speed'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'cast_speed' ? true : false);
                    break;
                default:
                    throw new RuntimeException('Invalid stat!');
            }

			// Verify training has minimum increase
			if ($lesson_modifier <= 1) {
				throw new RuntimeException('Invalid stat!');
            }

			// Verify player can afford training
			$player->subtractMoney($lesson_cost, "Paid " . $lesson_cost . "&yen; for lessons.");

            // Add yen to sensei
            $sensei_player = User::loadFromId($system, $lesson_sensei['sensei_id']);
            $sensei_player->loadData(User::UPDATE_NOTHING);
            $sensei_player->addMoney($lesson_cost / 5, "Earned " . $lesson_cost / 5 . "&yen; for lessons.");
            $sensei_player->updateData();

			// Set training for player
            $trainingManager = new TrainingManager($system, $player);
            switch ($lesson_data['train_type']) {
                case 'short':
                    $lesson_gain = $trainingManager->stat_train_gain * $lesson_modifier;
                    break;
                case 'long':
                    $lesson_gain = $trainingManager->stat_long_train_gain * $lesson_modifier;
                    break;
                case 'extended':
                    $lesson_gain = $trainingManager->stat_extended_train_gain * $lesson_modifier;
                    break;
                default:
                    throw new RuntimeException('Invalid duration!');
            }
            if ($player->total_stats >= $player->rank->stat_cap) {
                throw new RuntimeException("You cannot train any more at this rank!");
            }
            $player->log(User::LOG_TRAINING, "Type: {$lesson_data['lesson_stat']} / Length: {$lesson_duration}");
            $player->train_type = $lesson_data['lesson_stat'];
            $player->train_gain = $lesson_gain;
            $player->train_time = time() + $lesson_duration;

			// Create notification for player
            $new_notification = new NotificationDto(
                type: "training",
                message: "Training " . System::unSlug($lesson_data['lesson_stat']),
                user_id: $player->user_id,
                created: time(),
                duration: $lesson_duration,
                alert: false,
            );
            NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);

			// Log lesson
            if (!SenseiManager::logLesson($lesson_sensei['sensei_id'], $player->user_id, $lesson_duration, $lesson_sensei['temp_students'], (int)$lesson_sensei['yen_gained'] + ((int)$lesson_cost / 5), (int)$lesson_sensei['time_trained'] + (int)$lesson_duration, $system)) {
                throw new RuntimeException('Something went wrong!');
            }

			// Update
			$player->updateData();
            $system->message("Lesson started for " . $lesson_cost . "&yen;.");
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
	// If exam started
	if (isset($_GET['sensei_exam'])) {
		try {
			// check if already sensei
            if (SenseiManager::isActiveSensei($player->user_id, $system)) {
                throw new RuntimeException('You do not meet the requirements!');
            }
            // check rank
            if ($player->rank_num < 4) {
                throw new RuntimeException('You do not meet the requirements!');
            }
            // check level
            if ($player->level < 75) {
                throw new RuntimeException('You do not meet the requirements!');
            }
            // check justu mastered
            $mastered_count = 0;
            $player->getInventory();
            foreach ($player->jutsu as $jutsu) {
                if ($jutsu->level == 100) {
                    $mastered_count++;
                }
            }
            if ($mastered_count < 5) {
                throw new RuntimeException('You do not meet the requirements!');
            }
        }
		catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
		require 'templates/sensei_exam.php';
    }
	// Default
	else {
		$applications = [];
		// If Sensei
		if (SenseiManager::isActiveSensei($player->user_id, $system)) {
            $applications = SenseiManager::getApplicationsBySensei($player->user_id, $system);
        }
		// If eligible Student
		else if ($player->sensei_id == 0 && $player->rank_num < 3) {
            $applications = SenseiManager::getApplicationsByStudent($player->user_id, $system);
        }

        // If update recruitment settings - Old Profile
        $student_message_max_length = 500;
        $recruitment_message_max_length = 100;
        if (!empty($_POST['update_student_recruitment'])) {
            $recruitment_message = $system->db->clean($_POST['recruitment_message']);
            try {
                $enable_lessons;
                $player->accept_students = isset($_POST['accept_students']);
                $enable_lessons = isset($_POST['enable_lessons']);
                // Update recruitment settings
                SenseiManager::updateStudentRecruitment($player->user_id, $recruitment_message, $system);
                $success = SenseiManager::updateStudentLessons($player->user_id, (bool) $enable_lessons, $system);
                $system->message("Recruitment settings updated!");
            } catch (RuntimeException $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
            $player->updateData();
        }
        // If update student settings - Old Profile
        if (!empty($_POST['update_student_settings'])) {
            $student_message = $system->db->clean($_POST['student_message']);
            $specialization = $system->db->clean($_POST['specialization']);
            try {
                // Update student settings
                $success = SenseiManager::updateStudentSettings($player->user_id, $student_message, $specialization, $system);
                if (!$success) {
                    throw new RuntimeException('Something went wrong!');
                }
                $system->message("Student settings updated!");
            } catch (RuntimeException $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // If staff
        if (isset($_GET['village'])) {
            if ($player->staff_manager->isModerator()) {
                $sensei_list = SenseiManager::getSenseiByVillage($system->db->clean($_GET['village']), $system);
            } else {
                $sensei_list = SenseiManager::getSenseiByVillage($player->village->name, $system);
            }
        }
        // Default
		else {
			$sensei_list = SenseiManager::getSenseiByVillage($player->village->name, $system);
        }
		// Calculate lesson modifiers
        foreach ($sensei_list as &$sensei) {
            $sensei['ninjutsu_modifier'] = round(SenseiManager::getLessonModifier($player->ninjutsu_skill, $player->exp, $sensei['ninjutsu_skill'], $sensei['exp'], $sensei['specialization'] == 'ninjutsu_skill' ? true : false) * 100 - 100, 2);
            $sensei['taijutsu_modifier'] = round(SenseiManager::getLessonModifier($player->taijutsu_skill, $player->exp, $sensei['taijutsu_skill'], $sensei['exp'], $sensei['specialization'] == 'taijutsu_skill' ? true : false) * 100 - 100, 2);
            $sensei['genjutsu_modifier'] = round(SenseiManager::getLessonModifier($player->genjutsu_skill, $player->exp, $sensei['genjutsu_skill'], $sensei['exp'], $sensei['specialization'] == 'genjutsu_skill' ? true : false) * 100 - 100, 2);
            if ($player->bloodline_id == $sensei['bloodline_id']) {
                $sensei['bloodline_modifier'] = round(SenseiManager::getLessonModifier($player->bloodline_skill, $player->exp, $sensei['bloodline_skill'], $sensei['exp'], true) * 100 - 100, 2);
            }
            $sensei['speed_modifier'] = round(SenseiManager::getLessonModifier($player->speed, $player->exp, $sensei['speed'], $sensei['exp'], $sensei['specialization'] == 'speed' ? true : false) * 100 - 100, 2);
            $sensei['cast_speed_modifier'] = round(SenseiManager::getLessonModifier($player->cast_speed, $player->exp, $sensei['cast_speed'], $sensei['exp'], $sensei['specialization'] == 'cast_speed' ? true : false) * 100 - 100, 2);
            unset($sensei);
        }
        $lesson_cost = SenseiManager::getLessonCostForPlayer($player, $system);

        // Old Profile Section
        $sensei;
        $students = [];
        $temp_students = [];
        if ($player->sensei_id != 0) {
            // get sensei table data
            $sensei = SenseiManager::getSenseiByID($player->sensei_id, $system);
            // get student boost
            $sensei += SenseiManager::getStudentBoostBySensei($player->sensei_id, $system);
            // get sensei user data
            $sensei += SenseiManager::getSenseiUserData($player->sensei_id, $system);
        } else if (SenseiManager::isActiveSensei($player->user_id, $system)) {
            // get sensei table data
            $sensei = SenseiManager::getSenseiByID($player->user_id, $system);
            // get student boost
            $sensei += SenseiManager::getStudentBoostBySensei($player->user_id, $system);
            // if sensei has students, get student data
            if (count($sensei['students']) > 0) {
                $students = SenseiManager::getStudentData($sensei['students'], $system);
            }
            if (count($sensei['temp_students']) > 0) {
                $temp_students = SenseiManager::getTempStudentData($sensei['temp_students'], $system);
            }
        }

		require 'templates/sensei.php';
    }

	$system->printMessage();
}
?>
