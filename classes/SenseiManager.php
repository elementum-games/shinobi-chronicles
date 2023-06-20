<?php

class SenseiManager {
    public static array $boost_tiers = [
        0 => ['boost_primary' => 3, 'boost_secondary' => 0],
        3 => ['boost_primary' => 6, 'boost_secondary' => 3],
        8 => ['boost_primary' => 9, 'boost_secondary' => 4.5],
        14 => ['boost_primary' => 12, 'boost_secondary' => 6],
    ];

    public static array $exam_answers = ['1a', '2c', '3c', '4c', '5b', '6b'];

    const LESSON_COST_PER_MINUTE = 60;

    public static function scoreExam(array $answers, System $system): bool {
        $passed = true;
        foreach ($answers as $answer) {
            if (!in_array($answer, SenseiManager::$exam_answers)) {
                $passed = false;
            }
        }
        return $passed;
    }

    public static function getStudentBoost(int $graduated_count): array {
        $boost = [];
        foreach (self::$boost_tiers as $key => $tier) {
            if ($graduated_count >= $key) {
                $boost = $tier;
            }
        }
        return $boost;
    }

    public static function getStudentBoostBySensei(int $sensei_id, System $system): array {
        $sensei_result = $system->db->query(
            "SELECT `graduated_count`, `specialization`, `bloodline_id`, `name` FROM `sensei`
                LEFT JOIN `user_bloodlines` on `sensei`.`sensei_id` = `user_bloodlines`.`user_id`
                WHERE `sensei_id` = '{$sensei_id}'"
        );
        $result = $system->db->fetch($sensei_result);
        $boost = SenseiManager::getStudentBoost((int)$result['graduated_count']);
        $boost['specialization'] = $result['specialization'];
        $boost['bloodline_id'] = $result['bloodline_id'];
        $boost['bloodline_name'] = $result['name'];
        return $boost;
    }

    public static function getSenseiByVillage(string $village_name, System $system): array {
        $sensei_list = [];
        $sensei_table_result = $system->db->query(
            "SELECT `sensei`.`sensei_id`, `students`, `temp_students`, `recruitment_message`, `graduated_count`, `specialization`, `enable_lessons`, `avatar_link`, `user_name`, `ninjutsu_skill`, `taijutsu_skill`, `genjutsu_skill`, `bloodline_skill`, `speed`, `cast_speed`, `exp` FROM `sensei`
            INNER JOIN `users` ON `sensei`.`sensei_id` = `users`.`user_id`
            WHERE `village` = '{$village_name}' AND `accept_students` = '1' AND `is_active` = 1 ORDER BY `graduated_count` DESC"
        );
        while ($result = $system->db->fetch($sensei_table_result)) {
            $sensei_list[] = [
                'sensei_id' => $result['sensei_id'],
                'students' => json_decode($result['students']),
                'temp_students' => json_decode($result['temp_students'], true),
                'recruitment_message' => $result['recruitment_message'],
                'graduated' => $result['graduated_count'],
                'specialization' => $result['specialization'],
                'enable_lessons' => $result['enable_lessons'],
			    'avatar_link' => $result['avatar_link'],
			    'user_name' => $result['user_name'],
                'ninjutsu_skill' => $result['ninjutsu_skill'],
                'taijutsu_skill' => $result['taijutsu_skill'],
                'genjutsu_skill' => $result['genjutsu_skill'],
                'bloodline_skill' => $result['bloodline_skill'],
                'speed' => $result['speed'],
                'cast_speed' => $result['cast_speed'],
                'exp' => $result['exp'],
            ];
        }
		foreach ($sensei_list as &$sensei) {
            $sensei += SenseiManager::getStudentBoostBySensei($sensei['sensei_id'], $system);
			$sensei['students'] = SenseiManager::getStudentData($sensei['students'], $system);
            $sensei['temp_students'] = SenseiManager::checkTempStudents($sensei['sensei_id'], $system);
            $sensei['temp_students'] = SenseiManager::getTempStudentData($sensei['temp_students'], $system);
        }
        return $sensei_list;
    }

    public static function getSenseiByID(int $sensei_id, System $system): array {
        $sensei = [];
        $sensei_table_result = $system->db->query(
            "SELECT * FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'"
        );
        $result = $system->db->fetch($sensei_table_result);
        $sensei = array(
            'sensei_id' => $sensei_id,
            'students' => json_decode($result['students']),
            'recruitment_message' => $result['recruitment_message'],
            'graduated' => $result['graduated_count'],
            'student_message' => $result['student_message'],
            'specialization' => $result['specialization'],
            'graduated_students' => json_decode($result['graduated_students']),
            'temp_students' => json_decode($result['temp_students'], true),
            'time_trained' => $result['time_trained'],
            'yen_gained' => $result['yen_gained'],
            'is_active' => $result['is_active'],
            'enable_lessons' => $result['enable_lessons'],
        );
        return $sensei;
    }

    public static function getSenseiUserData(int $sensei_id, System $system): array {
        $sensei_data = [];
        $sensei_result = $system->db->query(
            "SELECT `user_name`, `users`.`user_id`, `avatar_link`, `village`, `accept_students`, `ninjutsu_skill`, `taijutsu_skill`, `genjutsu_skill`, `bloodline_skill`, `speed`, `cast_speed`, `exp`, `user_bloodlines`.`bloodline_id`, `user_bloodlines`.`name` FROM `users`
                LEFT JOIN `user_bloodlines` ON `users`.`user_id` = `user_bloodlines`.`user_id`
                WHERE `users`.`user_id` = '{$sensei_id}'"
        );
        $result = $system->db->fetch($sensei_result);
        $sensei_data['user_name'] = $result['user_name'];
        $sensei_data['user_id'] = $result['user_id'];
        $sensei_data['avatar_link'] = $result['avatar_link'];
        $sensei_data['village'] = $result['village'];
        $sensei_data['accept_students'] = $result['accept_students'];
        $sensei_data['ninjutsu_skill'] = $result['ninjutsu_skill'];
        $sensei_data['taijutsu_skill'] = $result['taijutsu_skill'];
        $sensei_data['genjutsu_skill'] = $result['genjutsu_skill'];
        $sensei_data['bloodline_skill'] = $result['bloodline_skill'];
        $sensei_data['speed'] = $result['speed'];
        $sensei_data['cast_speed'] = $result['cast_speed'];
        $sensei_data['exp'] = $result['exp'];
        $sensei_data['bloodline_name'] = $result['name'];
        $sensei_data['bloodline_id'] = $result['bloodline_id'];
        return $sensei_data;
    }

    public static function getStudentData(array $student_ids, System $system): array {
        $student_list = [];
        if (count($student_ids) > 0) {
            $student_result = $system->db->query(
                "SELECT `user_name`, `user_id`, `avatar_link` FROM `users` WHERE `user_id` IN (" . implode(",", $student_ids) . ")"
            );
            while($row = $system->db->fetch($student_result)) {
                array_push($student_list, (object) [
                        'user_name' => $row['user_name'],
                        'user_id' => $row['user_id'],
                        'avatar_link' => $row['avatar_link'],
                    ]
                );
            }
        }
        return $student_list;
    }

    public static function getApplicationsByStudent(int $student_id, System $system): array {
        $application_list = [];
        $application_result = $system->db->query(
            "SELECT `student_applications`.`sensei_id` as 'sensei_id', `users`.`user_name` FROM `student_applications`
                           INNER JOIN `users` ON `student_applications`.`sensei_id` = `users`.`user_id`
                           WHERE `student_applications`.`student_id` = '{$student_id}'"
        );
        while($row = $system->db->fetch($application_result)) {
            array_push($application_list, (object) [
                    'sensei_id' => $row['sensei_id'],
                    'sensei_name' => $row['user_name'],
                ]
            );
        }
        return $application_list;
    }

    public static function getApplicationsBySensei(int $sensei_id, System $system): array {
        $application_list = [];
        $application_result = $system->db->query(
            "SELECT `student_applications`.`student_id` as 'student_id', `users`.`user_name` FROM `student_applications`
                           INNER JOIN `users` ON `student_applications`.`student_id` = `users`.`user_id`
                           WHERE `student_applications`.`sensei_id` = '{$sensei_id}'"
        );
        while($row = $system->db->fetch($application_result)) {
            array_push($application_list, (object) [
                    'student_id' => $row['student_id'],
                    'student_name' => $row['user_name'],
                ]
            );
        }
        return $application_list;
    }

    public static function updateStudentRecruitment(int $sensei_id, $recruitment_message, System $system): bool {
        $db_modified = false;
        $system->db->query(
            "UPDATE `sensei` SET `recruitment_message` = '{$recruitment_message}' WHERE `sensei_id` = '{$sensei_id}'"
        );
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }
    public static function updateStudentLessons(int $sensei_id, bool $enable_lessons, System $system): bool
    {
        $system->db->query(
            "UPDATE `sensei` SET `enable_lessons` = '{$enable_lessons}' WHERE `sensei_id` = '{$sensei_id}'"
        );
        
        return $system->db->last_affected_rows > 0;
    }

    public static function updateStudentSettings(int $sensei_id, $student_message, $specialization, System $system): bool {
        $db_modified = false;
        $system->db->query(
            "UPDATE `sensei` SET `student_message` = '{$student_message}', `specialization` = '{$specialization}' WHERE `sensei_id` = '{$sensei_id}'"
        );
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function removeStudent(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $student_result = $system->db->query("SELECT `students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db->fetch($student_result);
        $students = json_decode($result['students']);
        $students = array_diff($students, array($student_id));
        $students = "[" . implode(', ', $students) . "]";
        $system->db->query("UPDATE `sensei` SET `students` = '{$students}' WHERE `sensei_id` = '{$sensei_id}'");
        $system->db->query("UPDATE `users` SET `sensei_id` = '0' WHERE `user_id` = '{$student_id}'");
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function addSensei(int $sensei_id, $specialization, System $system): bool {
        $db_modified = false;
        $system->db->query(
            "INSERT INTO `sensei` (`sensei_id`, `specialization`)
                VALUES ('{$sensei_id}', '{$specialization}')
                ON DUPLICATE KEY UPDATE `is_active` = 1;"
        );
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function removeSensei(int $sensei_id, System $system): bool {
        $db_modified = false;
        $system->db->query("UPDATE `users` SET `sensei_id` = '0' WHERE `sensei_id` = {$sensei_id}");
        $system->db->query("UPDATE `sensei` SET `is_active` = '0', `students` = '[]', `temp_students` = '[]' WHERE `sensei_id` = {$sensei_id}");
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        $system->db->query("DELETE FROM `student_applications` WHERE `sensei_id` = {$sensei_id}");
        return $db_modified;
    }

    public static function createApplication(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $student_result = $system->db->query("SELECT `students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db->fetch($student_result);
        $students = json_decode($result['students']);
        if (count($students) < 3) {
            $system->db->query(
                "INSERT INTO `student_applications` (`sensei_id`, `student_id`)
                SELECT '{$sensei_id}', '{$student_id}' FROM DUAL
                WHERE NOT EXISTS (SELECT 1 FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}' AND `student_id` = '{$student_id}')"
            );
        }
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function acceptApplication(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $student_result = $system->db->query("SELECT `students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db->fetch($student_result);
        $students = json_decode($result['students']);
        if (count($students) < 3) {
            array_push($students, $student_id);
            $student_count = count($students);
            $students = "[" . implode(', ', $students) . "]";
            $system->db->query("UPDATE `sensei` SET `students` = '{$students}' WHERE `sensei_id` = '{$sensei_id}'");
            $system->db->query("UPDATE `users` SET `sensei_id` = '{$sensei_id}' WHERE `user_id` = '{$student_id}'");
            if ($system->db->last_affected_rows > 0) {
                $db_modified = true;
            }
            $system->db->query(
                "DELETE FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}' AND `student_id` = '{$student_id}'"
            );
            SenseiManager::closeApplicationsByStudent($student_id, $system);
            if ($student_count == 3) {
                SenseiManager::closeApplicationsBySensei($sensei_id, $system);
            }
        }

        return $db_modified;
    }

    public static function closeApplication(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $system->db->query(
            "DELETE FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}' AND `student_id` = '{$student_id}'"
        );
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeApplicationsBySensei(int $sensei_id, System $system): bool {
        $db_modified = false;
        $system->db->query("DELETE FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}'");
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeApplicationsByStudent(int $student_id, System $system): bool {
        $db_modified = false;
        $system->db->query("DELETE FROM `student_applications` WHERE `student_id` = '{$student_id}'");
        if ($system->db->last_affected_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function incrementGraduatedCount(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $sensei_result = $system->db->query(
            "SELECT `graduated_count`, `graduated_students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'"
        );
        $result = $system->db->fetch($sensei_result);
        $graduated_students = json_decode($result['graduated_students']);
        if (!in_array($student_id, $graduated_students)) {
            array_push($graduated_students, $student_id);
            $graduated_students = "[" . implode(', ', $graduated_students) . "]";
            $graduated_count = (int)$result['graduated_count'];
            $graduated_count++;
            $system->db->query(
                "UPDATE `sensei` SET `graduated_count` = {$graduated_count}, `graduated_students` = '{$graduated_students}' WHERE `sensei_id` = '{$sensei_id}'"
            );
            if ($system->db->last_affected_rows > 0) {
                $db_modified = true;
            }
        }
        return $db_modified;
    }

    public static function hasStudents(int $sensei_id, System $system): bool {
        $hasStudents = false;
        $sensei_result = $system->db->query("SELECT 1 FROM `users` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db->fetch($sensei_result);
        if ($system->db->last_num_rows > 0) {
            $hasStudents = true;
        }
        return $hasStudents;
    }

    public static function hasApplications(int $sensei_id, System $system): bool {
        $hasApplications = false;
        $application_result = $system->db->query(
            "SELECT 1 FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}'"
        );
        $result = $system->db->fetch($application_result);
        if ($system->db->last_num_rows > 0) {
            $hasApplications = true;
        }
        return $hasApplications;
    }

    public static function isSensei(int $user_id, System $system): bool {
        $isSensei = false;
        $sensei_result = $system->db->query("SELECT 1 FROM `sensei` WHERE `sensei_id` = '{$user_id}'");
        $result = $system->db->fetch($sensei_result);
        if ($system->db->last_num_rows > 0) {
            $isSensei = true;
        }
        return $isSensei;
    }
    public static function isActiveSensei(int $user_id, System $system): bool
    {
        $isSensei = false;
        $sensei_result = $system->db->query("SELECT 1 FROM `sensei` WHERE `sensei_id` = '{$user_id}' AND `is_active` = 1");
        $result = $system->db->fetch($sensei_result);
        if ($system->db->last_num_rows > 0) {
            $isSensei = true;
        }
        return $isSensei;
    }

    public static function getLessonModifier(int $player_stat, int $player_exp, int $sensei_stat, int $sensei_exp, bool $is_specialization): float
    {
        $lesson_modifier = 2.0;
        // First 50% no penalty
        $stat_ratio_penalty = ($player_stat - ($sensei_stat / 2)) / ($sensei_stat / 2);
        $lesson_modifier -= max(0, $stat_ratio_penalty);
        // First 50% no penalty
        $exp_ratio_penalty = ($player_exp - ($sensei_exp / 2)) / ($sensei_exp / 2);
        $lesson_modifier -= max(0, $exp_ratio_penalty);
        // If no benefit, return minimum
        if ($lesson_modifier <= 1) {
            return 1;
        }
        // If specialization, cap at 2x else 1.75x
        return $is_specialization ? min(2.0, $lesson_modifier) : min(1.75, $lesson_modifier);
    }

    public static function getLessonCostForPlayer(User $player, System $system): array {
        $cost = [];
        $stat_train_length = 600;
	    // 56.25% of standard
	    $stat_long_train_length = $stat_train_length * 4;
        // 30x length (5 hrs), 12x gains: 40% of standard
        $stat_extended_train_length = $stat_train_length * 30;
	    // Forbidden seal trainings boost
        $stat_long_train_length *= $player->forbidden_seal->long_training_time;
        $stat_extended_train_length = round($stat_extended_train_length * $player->forbidden_seal->extended_training_time);
        // Minutes * cost/min * rank
        $standard_lesson_cost = ($stat_train_length / 60) * SenseiManager::LESSON_COST_PER_MINUTE * $player->rank_num;
        $long_lesson_cost = ($stat_long_train_length / 60) * SenseiManager::LESSON_COST_PER_MINUTE * $player->rank_num;
        $extended_lesson_cost = ($stat_extended_train_length / 60) * SenseiManager::LESSON_COST_PER_MINUTE * $player->rank_num;
        $cost['short'] = $standard_lesson_cost;
        $cost['long'] = $long_lesson_cost;
        $cost['extended'] = $extended_lesson_cost;
        return $cost;
    }

    public static function getLessonDurationForPlayer(User $player, System $system): array {
        $duration = [];
        $stat_train_length = 600;
        // 56.25% of standard
        $stat_long_train_length = $stat_train_length * 4;
        // 30x length (5 hrs), 12x gains: 40% of standard
        $stat_extended_train_length = $stat_train_length * 30;
        // Forbidden seal trainings boost
        $stat_long_train_length *= $player->forbidden_seal->long_training_time;
        $stat_extended_train_length = round($stat_extended_train_length * $player->forbidden_seal->extended_training_time);
        $duration['short'] = $stat_train_length;
        $duration['long'] = $stat_long_train_length;
        $duration['extended'] = $stat_extended_train_length;
        return $duration;
    }

    public static function hasSlot(int $sensei_id, System $system): bool {
        $slot_result = $system->db->query("SELECT `students`, `temp_students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db->fetch($slot_result);
        $students = json_decode($result['students']);
        $temp_students = json_decode($result['temp_students'], true);
        $student_count = count($students) + count($temp_students);

        return $student_count < 3 ? true : false;
    }

    public static function logLesson(int $sensei_id, int $temp_student_id, int $lesson_duration, array $temp_students, int $yen_gained, int $time_trained, System $system): bool {
        $db_modified = false;
        $temp_students[$temp_student_id] = time() + $lesson_duration;
        $temp_students = json_encode($temp_students, JSON_FORCE_OBJECT);
        $system->db->query("UPDATE `sensei` SET `temp_students` = '{$temp_students}', `yen_gained` = {$yen_gained}, `time_trained` = {$time_trained} WHERE `sensei_id` = '{$sensei_id}'");
        return $system->db->last_affected_rows > 0;
    }

    public static function checkTempStudents(int $sensei_id, System $system): array {
        $student_result = $system->db->query("SELECT `temp_students` FROM `sensei` WHERE `sensei_id` = {$sensei_id}");
        $result = $system->db->fetch($student_result);
        $student_list = json_decode($result['temp_students'], true);
        foreach ($student_list as $student_id => $train_end) {
            if ($train_end <= time()) {
                unset($student_list[$student_id]);
            }
        }
        $student_list_for_db = json_encode($student_list, JSON_FORCE_OBJECT);
        $student_result = $system->db->query("UPDATE `sensei` SET `temp_students` = '{$student_list_for_db}' WHERE `sensei_id` = {$sensei_id}");
        return $student_list;
    }

    public static function getTempStudentData(array $student_ids, System $system): array {
        $student_list = [];
        if (count($student_ids) > 0) {
            $student_result = $system->db->query(
                "SELECT `user_name`, `user_id`, `avatar_link` FROM `users` WHERE `user_id` IN (" . implode(",", array_keys($student_ids)) . ")"
            );
            while($row = $system->db->fetch($student_result)) {
                array_push($student_list, (object) [
                        'user_name' => $row['user_name'],
                        'user_id' => $row['user_id'],
                        'avatar_link' => $row['avatar_link'],
                    ]
                );
            }
        }
        return $student_list;
    }
}
