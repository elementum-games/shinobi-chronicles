<?php

interface Student{
    public function getSenseiID(): int|null;
    public function setSenseiID(Int $id): void;
    public function deleteSenseiID(int $id): void;
    public function addToSenseiSkillAmount(int $amount): void;

}

interface Sensei{
    public function getStudentInformation(): array;
    public function setStudentIDS(array $ids): void;
    public function deleteStudentID(int $id): void;
}

/**
 * Sensei Manager Handles DB interaction and holds properties for students and teachers
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
        //if 
        if ($this->system->db_last_num_rows != 0) {
            $result = $this->system->db_fetch($result);
            $this->sensei_id = $result['sensei_id'];
            $this->isSensei = true;
            $this->isStudent = false;
        } else {

            $result = $this->system->query("SELECT `rank` from `users` WHERE `user_id`='$this->user_id' LIMIT 1");
            $user_rank = $this->system->db_fetch($result);

            if($this->system->db_last_num_rows != 0){
                $user_rank = $user_rank['rank'];
            }

            if ($user_rank > 2) {
            $this->isSensei = true;
            $this->isStudent = false;   

            $this->system->query("INSERT INTO `sensei_list` (`sensei_id`, `assoc_user_id`, `student_list`, `teaching_boost_amount`, `sensei_skill`)
            VALUES ('0', '{$user_id}', '" . json_encode([]) . "', '1.00', '0')");
            
            } else {
                $this->isSensei = false;
                $this->isStudent = false;
            }
        }
        

    }


    public function getSenseiSkillAmount(): int{
        return $this->sensei_skill;
    }

    public function isSensei(){
        return $this->isSensei;
    }

    public function isStudent(){
        return $this->isStudent;
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
	 */
	public function getSenseiID(): int|null {
        return $this->sensei_id;
	}
	
	/**
	 *
	 * @param int $id
	 */
	public function setSenseiID(int $id): void {
	}
	
	/**
	 *
	 * @param int $id
	 */
	public function deleteSenseiID(int $id): void {
	}
	
	/**
	 *
	 * @param int $amount
	 */
	public function addToSenseiSkillAmount(int $amount): void {
	}
}