<?php

//Regarding the Interfaces, I was meant to split the SenseiManager into two.
//I'm thinking about using a SenseiManagerFactory to handle whether the USER is a Student or a Teacher and have different classes to manage different functions but I think i'll do that later. I'm still learning about Factory implimentation. 

interface Student{
    public function getMySenseisID(): int|null;
    public function setMySenseisIDinDB(Int $id): void;
    public function deleteMySenseisID(int $id): void;
    public function addToMySenseiSkillAmount(int $amount): void;
    public function checkIfRegisteredStudent(): bool;

}

interface Sensei{
    public function getStudentInformation(): array;
    public function setStudentIDS(array $ids): void;
    public function deleteStudentID(int $id): void;
    public function checkIfRegisteredSensei(): bool;
}

/**
 * SenseiManager Handles Database(DB) interaction from within the USER object regarding all Sensei/Student interaction such as Registration/Deletion.
 * This class also returns properties used to update properties within the USER class to be shown globally. 
 */
class SenseiManager implements Sensei, Student{

    private int|null $myTeachingID;
    private int|null $mySenseisID;
    private int $student_id;
    private System $system;
    private int $sensei_skill;
    private bool $isSensei;
    private bool $isStudent;
    private int $user_id;
    private int $user_rank;
    private int $default_teacher_boost_amount;

    public function __construct(System $system, int $user_id){

        $this->myTeachingID = 0;
        $this->mySenseisID = 0;
        $this->student_id = 0;
        $this->isSensei = false;
        $this->isStudent = false;
        $this->system = $system;
        $this->sensei_skill = 0;
        $this->user_id = $user_id;
        $this->user_rank = -1;
        $this->default_teacher_boost_amount = 1.05;

        //Grab [Rank] from DB 
        $result = $this->system->query("SELECT `rank` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
        $user_rank = $this->system->db_fetch($result);
        if($this->system->db_last_num_rows != 0){
            $this->user_rank = $user_rank['rank'];
        }

        //Academy Student / Genin
        if ($this->user_rank <= 2) {
            //check if student has a Sensei
            $result = $this->system->query("SELECT `my_senseis_id` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
            $sensei_object = $this->system->db_fetch($result);
            //If Sensei ID found && Set Data
            if ($this->system->db_last_num_rows != 0 && $sensei_object['my_senseis_id'] != null) {
                $this->allocateStudentData($sensei_object['my_senseis_id']);
                return;
            }
        }

        //Chuunin+
        if ($this->user_rank > 2) {
            //Search for Teaching ID
            $result = $this->system->query(
                "SELECT `sensei_id` FROM `sensei_list` WHERE `assoc_user_id`='$user_id' LIMIT 1"
            );
            //if Sensei ID found -> set Sensei Status
            if ($this->system->db_last_num_rows != 0) {
                $result = $this->system->db_fetch($result);
                $this->allocateTeachingData($result['sensei_id']);
            }
        }
    }

    /**
     * The USER's User_ID is passed through various filters before being allowed to register as a new sensei in the 'sensei_list' DB table
     * @param int $user_id
     * @return void
     */
    public function registerNewTeacher(int $user_id){
        if ($this->user_rank > 2) {
            //Search for Teaching ID
            $result = $this->system->query(
                "SELECT `sensei_id` FROM `sensei_list` WHERE `assoc_user_id`='$user_id' LIMIT 1"
            );
            //if Sensei ID found -> set Sensei Status
            if ($this->system->db_last_num_rows != 0) {
                $this->system->message(("You cannot register twice!"));
                $this->system->printMessage();
                return;
            }

            //pass checks -> register
            $this->insertNewSenseiDataIntoDB($user_id);
            $this->system->message("Congratulations! You are now registered as a Sensei!");
            $this->system->printMessage();
            return;
        }

        //not high enough rank
        if($this->user_rank < 2){
            $this->system->message("You're not high enough rank to register as a Sensei!");
            $this->system->printMessage();
            return;
        }
    }

    /**
     * Insert a NEW teacher into the 'sensei_list' table.
     * registerANewTeacher() - is for the user to pass through various filters and should be called instead of this function.
     * this function should only be called from inside registerANewTeacher()
     * @param int $user_id
     * @return void
     */
    private function insertNewSenseiDataIntoDB(int $user_id){
        $this->system->query("INSERT INTO `sensei_list` (`sensei_id`, `assoc_user_id`, `student_list`, `teaching_boost_amount`, `sensei_skill`)
        VALUES ('0', '{$user_id}', '" . json_encode([]) . "', '$this->default_teacher_boost_amount', '0')");

        $this->system->query("UPDATE users SET isRegisteredSensei = 1 WHERE user_id = ${user_id}");
    }

    /**
     * Updates the Student's Sensei ID to a new Sensei
     * @param int $id
     * @return void
     */
    private function allocateStudentData(int $id){
        $this->mySenseisID = $id;
        $this->isSensei = false;
        $this->isStudent = true;
    }

    private function allocateTeachingData(int $id){
        $this->myTeachingID = $id;
        $this->isSensei = true;
        $this->isStudent = false;
    }


    public function getMySenseiSkillAmount(): int{
        return $this->sensei_skill;
    }

	/**
     * Returns a decoded JSON array of student information
     * 
	 * @return array
	 */
	public function getStudentInformation(): array {

        // $result = $this->system->query("SELECT `student_list` from `sensei_list` WHERE `assoc_user_id`='$this->user_id' LIMIT 1");
        // $studentList = $this->system->db_fetch($result);

        // //if sensei_id is found
        // if($this->system->db_last_num_rows != 0){
        //     $studentList = $studentList['student_list'];

        //     return json_decode($studentList, true); //array
        // }   
        
        // return []; //default

        
        return json_decode('{
            "names": 
                [
                    {"name": "Educba", "rank": "1", "skill_points_earned": "0", "avatar_link": "./images/default_avatar.png"},
                    {"name": "Snehal", "rank" : "2", "skill_points_earned": "0", "avatar_link": "./images/default_avatar.png"},
                    {"name": "Amardeep", "rank" : "1", "skill_points_earned": "0", "avatar_link": "./images/default_avatar.png"}
                ]
            }', true ); //Saving this here for testing
	}
	
	/**
	 *
	 * @param array $ids
	 */
	public function setStudentIDS(array $ids): void {
	}
	
	/**
	 *
	 * @param int $id
	 */
	public function deleteStudentID(int $id): void {

	}

    /**
     * If User is a practicing Teacher, this function will return their Teaching ID within the `sensei_list` table | otherwise NULL.
     * @return int|null
     */
    public function getMyTeachingID(): int|null {
        $result = $this->system->query("SELECT `sensei_id` from `sensei_list` WHERE `assoc_user_id`='$this->user_id' LIMIT 1");
        $senseiId = $this->system->db_fetch($result);

        //if sensei_id is found
        if($this->system->db_last_num_rows != 0){
            $senseiId = $senseiId['sensei_id'];

            return $senseiId; //int
        }

        return null; //no id was found
	}

	/**
     * If this User is a Student, this function will return their sensei's ID from inside the `user` table.
	 * @return int|null
	 */
	public function getMySenseisID(): int|null {
        $result = $this->system->query("SELECT `my_senseis_id` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
        $senseiId = $this->system->db_fetch($result);

        //if sensei_id is found
        if($this->system->db_last_num_rows != 0){
            $senseiId = $senseiId['my_senseis_id'];

            return $senseiId; //int
        }

        return null;
	}
	
	/**
	 * If this User is a Student this function will update/set the ID of their Sensei within the `user` table.
	 * @param int $id The Sensei's Teaching ID - not their personal USER_ID
	 */
	public function setMySenseisIDinDB(int $id): void {
        $this->system->query("UPDATE `users` (`my_senseis_id`)
            VALUES ('{$id}') WHERE user_id = {$this->user_id}");

        $this->isStudent = true;
        $this->isSensei = false;
	}
	
	/**
	 * If Student, removes their Sensei's ID from the Student's `user` table;
	 * @param int $id
	 */
	public function deleteMySenseisID(int $id): void {
	}
	
	/**
	 * Add to the Sensei's Total Skill Amount when called
	 * @param int $amount
	 */
	public function addToMySenseiSkillAmount(int $amount): void {
	}

    //TODO: I could probably combine the two sensei/student registry check DB calls
	/**
	 * @return bool
	 */
	public function checkIfRegisteredSensei(): bool {

        //check for status
        $result = $this->system->query("SELECT `isRegisteredSensei` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
        $registryStatus = $this->system->db_fetch($result);

        //if sensei_id is found
        if($this->system->db_last_num_rows != 0 && $registryStatus['isRegisteredSensei']){
            return $registryStatus['isRegisteredSensei']; //true or false
        }

        return false; //default
	}
    
	/**
	 * @return bool
	 */
	public function checkIfRegisteredStudent(): bool {

        //check for status
        $result = $this->system->query("SELECT `isRegisteredStudent` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
        $registryStatus = $this->system->db_fetch($result);

        //if sensei_id is found
        if($this->system->db_last_num_rows != 0){
            $registryStatus = $registryStatus['isRegisteredStudent'];
            return $registryStatus; //true or false
        }

        return false; //default
	}
}