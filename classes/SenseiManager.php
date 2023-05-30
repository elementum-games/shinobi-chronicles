<?php

class SenseiManager {
    public static array $boost_tiers = [
        0 => ['boost_primary' => 3, 'boost_secondary' => 0],
        3 => ['boost_primary' => 6, 'boost_secondary' => 3],
        8 => ['boost_primary' => 9, 'boost_secondary' => 4.5],
        14 => ['boost_primary' => 12, 'boost_secondary' => 6],
    ];

    public static array $exam_answers = ['1a', '2c', '3c', '4c', '5b', '6b'];

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
        $sensei_result = $system->query("SELECT `graduated_count`, `specialization` FROM `sensei` where `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($sensei_result);
        $boost = SenseiManager::getStudentBoost((int)$result['graduated_count']);
        $boost['specialization'] = $result['specialization'];
        return $boost;
    }

    public static function getSenseiByVillage(string $village_name, System $system): array {
        $sensei_list = [];
        $sensei_table_result = $system->query(
			"SELECT `sensei`.`sensei_id`, `students`, `recruitment_message`, `graduated_count`, `specialization`, `avatar_link`, `user_name`, `bloodline_name` FROM `sensei`
			INNER JOIN `users` ON `sensei`.`sensei_id` = `users`.`user_id`
			WHERE `village` = '{$village_name}' AND `accept_students` = '1' ORDER BY `graduated_count` DESC");
        while ($result = $system->db_fetch($sensei_table_result)) {
            $sensei_list[] = [
                'sensei_id' => $result['sensei_id'],
                'students' => json_decode($result['students']),
                'recruitment_message' => $result['recruitment_message'],
                'graduated' => $result['graduated_count'],
                'specialization' => $result['specialization'],
			    'avatar_link' => $result['avatar_link'],
			    'user_name' => $result['user_name'],
			    'bloodline_name' => $result['bloodline_name']
            ];
        }
		foreach ($sensei_list as &$sensei) {
            $sensei += SenseiManager::getStudentBoost($sensei['graduated'], $system);
			$sensei['students'] = SenseiManager::getStudentData($sensei['students'], $system);
        }
        return $sensei_list;
    }

    public static function getSenseiByID(int $sensei_id, System $system): array {
        $sensei = [];
        $sensei_table_result = $system->query("SELECT `students`, `recruitment_message`, `student_message`, `graduated_count`, `specialization` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($sensei_table_result);
        $sensei = array(
            'sensei_id' => $sensei_id,
            'students' => json_decode($result['students']),
            'recruitment_message' => $result['recruitment_message'],
            'student_message' => $result['student_message'],
            'graduated' => $result['graduated_count'],
            'specialization' => $result['specialization']
        );
        return $sensei;
    }

    public static function getSenseiUserData(int $sensei_id, System $system): array {
        $sensei_data = [];
        $sensei_result = $system->query("SELECT `user_name`, `user_id`, `avatar_link`, `bloodline_name` FROM `users` WHERE `user_id` = '{$sensei_id}'");
        $result = $system->db_fetch($sensei_result);
        $sensei_data['user_name'] = $result['user_name'];
        $sensei_data['user_id'] = $result['user_id'];
        $sensei_data['avatar_link'] = $result['avatar_link'];
        $sensei_data['bloodline_name'] = $result['bloodline_name'];
        return $sensei_data;
    }

    public static function getStudentData(array $student_ids, System $system): array {
        $student_list = [];
        if (count($student_ids) > 0) {
            $student_result = $system->query("SELECT `user_name`, `user_id`, `avatar_link` FROM `users` WHERE `user_id` IN (" . implode(",", $student_ids) . ")");
            while($row = $system->db_fetch($student_result)) {
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
        $application_result = $system->query("SELECT `student_applications`.`sensei_id` as 'sensei_id', `users`.`user_name` FROM `student_applications`
                       INNER JOIN `users` ON `student_applications`.`sensei_id` = `users`.`user_id`
                       WHERE `student_applications`.`student_id` = '{$student_id}'");
        while($row = $system->db_fetch($application_result)) {
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
        $application_result = $system->query("SELECT `student_applications`.`student_id` as 'student_id', `users`.`user_name` FROM `student_applications`
                       INNER JOIN `users` ON `student_applications`.`student_id` = `users`.`user_id`
                       WHERE `student_applications`.`sensei_id` = '{$sensei_id}'");
        while($row = $system->db_fetch($application_result)) {
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
        $system->query("START TRANSACTION;");
        $system->query("UPDATE `sensei` SET `recruitment_message` = '{$recruitment_message}' WHERE `sensei_id` = '{$sensei_id}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function updateStudentSettings(int $sensei_id, $student_message, $specialization, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("UPDATE `sensei` SET `student_message` = '{$student_message}', `specialization` = '{$specialization}' WHERE `sensei_id` = '{$sensei_id}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function removeStudent(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $student_result = $system->query("SELECT `students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($student_result);
        $students = json_decode($result['students']);
        $students = array_diff($students, array($student_id));
        $students = "[" . implode(', ', $students) . "]";
        $system->query("UPDATE `sensei` SET `students` = '{$students}' WHERE `sensei_id` = '{$sensei_id}'");
        $system->query("UPDATE `users` SET `sensei_id` = '0' WHERE `user_id` = '{$student_id}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function addSensei(int $sensei_id, $specialization, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("INSERT INTO `sensei` (`sensei_id`, `specialization`) VALUES ('{$sensei_id}', '{$specialization}')");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function removeSensei(int $sensei_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("UPDATE `users` SET `sensei_id` = '0' WHERE `sensei_id` = {$sensei_id}");
        $system->query("DELETE FROM `sensei` WHERE `sensei_id` = {$sensei_id}");
        $system->query("DELETE FROM `student_applications` WHERE `sensei_id` = {$sensei_id}");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function createApplication(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $student_result = $system->query("SELECT `students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($student_result);
        $students = json_decode($result['students']);
        if (count($students) < 3) {
            $system->query("START TRANSACTION;");
            $system->query("INSERT INTO `student_applications` (`sensei_id`, `student_id`)
            SELECT '{$sensei_id}', '{$student_id}' FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}' AND `student_id` = '{$student_id}')");
            $system->query("COMMIT;");
        }
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function acceptApplication(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $student_result = $system->query("SELECT `students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($student_result);
        $students = json_decode($result['students']);
        if (count($students) < 3) {
            array_push($students, $student_id);
            $student_count = count($students);
            $students = "[" . implode(', ', $students) . "]";
            $system->query("UPDATE `sensei` SET `students` = '{$students}' WHERE `sensei_id` = '{$sensei_id}'");
            $system->query("UPDATE `users` SET `sensei_id` = '{$sensei_id}' WHERE `user_id` = '{$student_id}'");
            $system->query("DELETE FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}' AND `student_id` = '{$student_id}'");
            $system->query("COMMIT;");
            SenseiManager::closeApplicationsByStudent($student_id, $system);
            if ($student_count == 3) {
                SenseiManager::closeApplicationsBySensei($sensei_id, $system);
            }
        }
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeApplication(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("DELETE FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}' AND `student_id` = '{$student_id}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeApplicationsBySensei(int $sensei_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("DELETE FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeApplicationsByStudent(int $student_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("DELETE FROM `student_applications` WHERE `student_id` = '{$student_id}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function incrementGraduatedCount(int $sensei_id, int $student_id, System $system): bool {
        $db_modified = false;
        $sensei_result = $system->query("SELECT `graduated_count`, `graduated_students` FROM `sensei` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($sensei_result);
        $graduated_students = json_decode($result['graduated_students']);
        if (!in_array($student_id, $graduated_students)) {
            array_push($graduated_students, $student_id);
            $graduated_students = "[" . implode(', ', $graduated_students) . "]";
            $graduated_count = (int)$result['graduated_count'];
            $graduated_count++;
            $system->query("START TRANSACTION;");
            $system->query("UPDATE `sensei` SET `graduated_count` = {$graduated_count}, `graduated_students` = '{$graduated_students}' WHERE `sensei_id` = '{$sensei_id}'");
            $system->query("COMMIT;");
            if ($system->db_last_num_rows > 0) {
                $db_modified = true;
            }
        }
        return $db_modified;
    }

    public static function hasStudents(int $sensei_id, System $system): bool {
        $hasStudents = false;
        $sensei_result = $system->query("SELECT 1 FROM `users` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($sensei_result);
        if ($system->db_last_num_rows > 0) {
            $hasStudents = true;
        }
        return $hasStudents;
    }

    public static function hasApplications(int $sensei_id, System $system): bool {
        $hasApplications = false;
        $application_result = $system->query("SELECT 1 FROM `student_applications` WHERE `sensei_id` = '{$sensei_id}'");
        $result = $system->db_fetch($application_result);
        if ($system->db_last_num_rows > 0) {
            $hasApplications = true;
        }
        return $hasApplications;
    }

    public static function isSensei(int $user_id, System $system): bool {
        $isSensei = false;
        $sensei_result = $system->query("SELECT 1 FROM `sensei` WHERE `sensei_id` = '{$user_id}'");
        $result = $system->db_fetch($sensei_result);
        if ($system->db_last_num_rows > 0) {
            $isSensei = true;
        }
        return $isSensei;
    }
}
