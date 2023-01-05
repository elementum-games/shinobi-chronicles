<?php

interface Student{
    public function getMySenseisID(): int|null;
    public function setMySenseisIDinDB(Int $id): void;
    public function deleteMySenseiID(int $id): void;
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
 * Sensei Manager Handles DB interaction and holds properties for students and teachers
 * 
 * This object can only be used and set during User construction
 * 
 */
class SenseiManager implements Sensei, Student{

    private int $sensei_id;
    private int $student_id;
    private System $system;
    private int $sensei_skill;
    private bool $isSensei;
    private bool $isStudent;
    private int $user_id;

    public function __construct(System $system, int $user_id){
        $this->sensei_id = 0;
        $this->student_id = 0;
        $this->system = $system;
        $this->sensei_skill = 0;
        $this->user_id = $user_id;

        //Search for Sensei ID
        $result = $this->system->query("SELECT `sensei_id` FROM `sensei_list` WHERE `assoc_user_id`='$user_id' LIMIT 1"
        );

        //if Sensei ID found -> set Sensei Status
        if ($this->system->db_last_num_rows != 0) {
            $result = $this->system->db_fetch($result);
            $this->sensei_id = $result['sensei_id'];
            $this->isSensei = true;
            $this->isStudent = false;

        //if No Sensei ID found
        } else {

            $result = $this->system->query("SELECT `rank` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
            $user_rank = $this->system->db_fetch($result);

            if($this->system->db_last_num_rows != 0){
                $user_rank = $user_rank['rank'];
            }

            //check if rank is high enough for Sensei Status
            if ($user_rank > 2) {
            $this->isSensei = true;
            $this->isStudent = false;   

            $this->system->query("INSERT INTO `sensei_list` (`sensei_id`, `assoc_user_id`, `student_list`, `teaching_boost_amount`, `sensei_skill`)
            VALUES ('0', '{$user_id}', '" . json_encode([]) . "', '1.00', '0')");
            
            //if rank is not high enough set default not sensei/ not student status
            } else {
                $this->isSensei = false;
                $this->isStudent = false;
            }

            //check if student
            $result = $this->system->query("SELECT `sensei_id` from `users` WHERE `sensei_id`='$this->user_id' LIMIT 1");
            $sensei_object = $this->system->db_fetch($result);

            //if sensei_id is found
            if($this->system->db_last_num_rows != 0){
                $sensei_object = $sensei_object['sensei_id'];
                $this->sensei_id = $sensei_object;
            } else {
                return true;
            }

            $this->isStudent = true;
            $this->isSensei = false;
        }
        

    }

    public function isSensei(){
        return $this->isSensei;
    }
    public function isStudent(){
        return $this->isStudent;
    }


    public function getMySenseiSkillAmount(): int{
        return $this->sensei_skill;
    }

	/**
	 * @return array
	 */
	public function getStudentInformation(): array {
        return json_decode('{ "names": [
            {"name": "Educba", "rank": "1"},
            {"name": "Snehal"},
            {"name": "Amardeep"}
        ]
        }', true);
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
	 * @return int|null
     * 
     * Grabs Teacher ID located in student's DB
	 */
	public function getMySenseisID(): int|null {
        $result = $this->system->query("SELECT `sensei_id` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
        $senseiId = $this->system->db_fetch($result);

        //if sensei_id is found
        if($this->system->db_last_num_rows != 0){
            $senseiId = $senseiId['sensei_id'];

            return $senseiId; //int
        }

        return null;
	}
	
	/**
	 *
	 * @param int $id
	 */
	public function setMySenseisIDinDB(int $id): void {
        $this->system->query("INSERT INTO `users` (`sensei_id`)
            VALUES ('{$id}')");

        $this->isStudent = true;
        $this->isSensei = false;
	}
	
	/**
	 *
	 * @param int $id
	 */
	public function deleteMySenseiID(int $id): void {
	}
	
	/**
	 *
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
        if($this->system->db_last_num_rows != 0){
            $registryStatus = $registryStatus['isRegisteredSensei'];

            return $registryStatus; //true or false
        }

        return false; //default setting
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

        return false; //default setting
	}
}