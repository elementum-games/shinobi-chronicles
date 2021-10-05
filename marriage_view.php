<?php
if(!isset($_SESSION['user_id'])) {
	exit;
}


//fires when user first enters page
function marriage_controller(){

	global $player;
	global $system;


	/**
	If player is Married -> Display Marriage View
	**/
	if($player->isMarried){
		//divorce time
		if(isset($_POST['divorce']) && $_POST['divorce'] == 'Divorce'){
			$marriage_IDs = Marriage_DatabaseInteraction::getMarriageIDsfromMarriageTable($player->marriageId);
			Marriage_DatabaseInteraction::divorce($marriage_IDs[0], $marriage_IDs[1]);

			header("Refresh:0"); //don't know if this is okay...
			return; //reload page
		}

		//find marriage ID
		//if ID invalid -> break
		$view = new MarriageView($player->marriageData);
		$view->display();
	}
	else {
		/**Player isn't married -> Check for Input from Forms -> No Input -> Display InputForms from MarriageModel**/

		//(Accept/Deny) - A Proposal
		if(isset($_POST['accept_proposal'])){
			$proposal_user = $system->clean($_POST['accept_proposal']); //holds name of user 2(proposing)\

			if($proposal_user !== 'no'){

				//get user id of proposal answer
				$user2_id_query = $system->query("SELECT `user_id` FROM `users`
									WHERE `user_name` = '{$proposal_user}'");
				$user2_id_result = $system->db_fetch($user2_id_query);
				$user2_id = $user2_id_result['user_id'];

				//create marriage ID assigns ID to both parties
				// TODO: Users2 is not being set correctly, check it out later
				$system->query(
						"INSERT INTO `marriage`
								(
								 `user1_id`,
								 `user2_id`
							 ) VALUES
							 (
								'$player->user_id',
								'$user2_id'
								)"
				);
			  MarriageModel::msg($system->db_insert_id);
				$marriageId = $system->db_insert_id;

				//User1 set married status to True, and set new marriage ID
				Marriage_DatabaseInteraction::setMarriageStatusToTrue_username($proposal_user);
				Marriage_DatabaseInteraction::setMarriageId_username($marriageId, $proposal_user);

				//User2 set married status to True and set new marriage ID
				Marriage_DatabaseInteraction::setMarriageStatusToTrue_username($player->user_name);
				Marriage_DatabaseInteraction::setMarriageId_userId($marriageId, $player->user_id);

				//clear inbox
				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `user_name` = '{$player->getName()}' LIMIT 1");
				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `user_name` = '{$proposal_user}' LIMIT 1");

				//erase all proposals from either party
				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `proposalInbox` = '{$proposal_user}' LIMIT 1");
				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `proposalInbox` = '{$player->getName()}' LIMIT 1");


				MarriageModel::msg("Woah you're married now!");

				header("Refresh:0"); //don't know if this is okay...
				return; //reload page
			}
			else {
				//answer was 'no'
				//grab user2 name
				$proposal_from_name = $system->query("SELECT `proposalInbox` FROM `users`
									WHERE `user_id` = '{$player->user_id}'");
				$proposal_from_name = $system->db_fetch($proposal_from_name); //holds username of proposal person

				//erase proposal inbox from current user and then reset other user marriageId to 0 so they can propose again
				$system->query("UPDATE `users` SET
					`marriage_id` = '0'
				WHERE `user_name` = '{$proposal_from_name['proposalInbox']}' LIMIT 1");

				//clear current users proposal inbox allowing them to have proposals again!
				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `user_name` = '{$player->getName()}' LIMIT 1");

				MarriageModel::msg("You rejected them");
			}
		}
		//(Accept/Deny) - A Proposal


		/**If users Proposal Inbox has a proposal -> display a [accept yes/no]**/
		$proposalInbox = $system->query("SELECT `proposalInbox` FROM `users`
							WHERE `user_id` = '{$player->user_id}'");
		$proposalInbox = $system->db_fetch($proposalInbox);
		if($proposalInbox['proposalInbox'] != ''){
			$marriageDepartment = new MarriageModel(Null);
			$marriageDepartment->displayAcceptProposalBox_yes_no($proposalInbox['proposalInbox']);
		}
		/**If users Proposal Inbox has a proposal -> display a [accept yes/no]**/


		//If user pressed 'yes' to propose to someone
		if(isset($_POST['marriage_radio_btn']) && $_POST['marriage_radio_btn'] !== 'no'){
			$m_temp = $system->clean($_POST['marriage_radio_btn']);

			//get user id of user they want to propose to
			$proposee_userId = $system->query("SELECT `user_id`, `proposalInbox` FROM `users`
								WHERE `user_name` = '{$m_temp}'");
			$proposee_name = $system->db_fetch($proposee_userId);

			//update proposal inbox with username of person who wants to propose
			//if proposal already in inbox don't send proposal
			if($proposee_name['proposalInbox'] == ''){

				//proposal inbox
				$system->query("UPDATE `users` SET
					`proposalInbox` = '{$player->getName()}'
				WHERE `user_id` = '{$proposee_name['user_id']}' LIMIT 1");


				//set current user marriageId to -1 to signify they've placed a proposal
				$system->query("UPDATE `users` SET
					`marriage_id` = '-1'
				WHERE `user_id` = '{$player->user_id}' LIMIT 1");

				MarriageModel::msg("Proposal Sent!");
			}
			else {
				MarriageModel::msg("{$m_temp} already has a proposal in their inbox :(!");
			}

		}
		//If user pressed 'yes' to propose to someone

		//if page reloaded with a username search to check for availability
		$temp_proposal_name = Null;
		if(isset($_POST['proposal_name'])){
			$temp_proposal_name = $system->clean($_POST['proposal_name']);

			if(strtolower($temp_proposal_name) == strtolower($player->getName())) {
				MarriageModel::msg("You can't marry yourself...");
				$temp_proposal_name = Null;
			}
		}
		//if page reloaded with a username search to check for availability

		//display stuff if married or not
		$marriageDepartment = new MarriageModel($temp_proposal_name);
		$temp_proposal_name = $marriageDepartment->checkDB_setProposalName($temp_proposal_name);

		//default - display search box
		if($temp_proposal_name == Null) $marriageDepartment->displaySearchBox();

	}

	// idk what happens when user gets here lol
}

/**
* @param String $tablename displays title of the html table
* @param Array $m_stats holds marriage stats i.e boosts
* @param Array $m_data holds marriage 'info' i.e dates/number
*/
class MarriageView{

  public $tablename = 'Marriage Panel';

	private $user1_profile_img = '';
	private $user2_profile_img = '';
	private $user1_username = '';
	private $user2_username = '';

	private $boost = 0;
	private $marriage_date = '0000-00-00';

	//holds marriage data
	private $m_stats = array(
		'regen boost' => 0,
	);
	private $m_data = array(
		'marriage date' => '0000-00-00',
		// 'weeks married' => '2'
	);

	function __construct($marriageData){

		//get marriage data from $User->marriageData
		$this->boost = $marriageData['boost_amount'];
		$this->marriage_date = $marriageData['proposal_date'];

		//set Object variables
		$this->m_stats['regen boost'] = $this->boost;
		$this->m_data['marriage date'] = $this->marriage_date;

		//profile pictures and names
		$this->getExtraProfileData($marriageData['user1_id'], $marriageData['user2_id']);

	}

	function getExtraProfileData($user1_id, $user2_id){

		global $system;

		$user1data = $system->query("SELECT `user_name`, `avatar_link` FROM `users`
							WHERE `user_id` = '{$user1_id}'");
		$user1result = $system->db_fetch($user1data);
		// print_r($user1result);

		$user2data = $system->query("SELECT `user_name`, `avatar_link` FROM `users`
							WHERE `user_id` = '{$user2_id}'");
		$user2result = $system->db_fetch($user2data);
		// print_r($user2result);

		$this->user1_profile_img = $user1result['avatar_link'];
		$this->user2_profile_img = $user2result['avatar_link'];;
		$this->user1_username = $user1result['user_name'];;
		$this->user2_username = $user2result['user_name'];

	}

  function display(){
    echo "
    <div>
      <table class='table'>
      <th colspan='2'>{$this->tablename}</th>

				<!--Display Area-->

        <tr>
          <td colspan='2'>

						<div id='niceview' style='display: grid; grid-template-columns: 1fr 0.3fr 1fr; justify-items: center; align-items: center; padding: 1em 0.5em;'>
							<!--USER1--> <img src=\"{$this->user1_profile_img}\" style=\"margin-top:5px;max-width:150px;max-height:150px;\">

				      <div style='width: 4em'>
								<!--I drew this<3-->
								<!--HEART-->
				        <svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" version=\"1.1\" id=\"Layer_1\" x=\"0px\" y=\"0px\" viewBox=\"0 0 50 50\" style=\"enable-background:new 0 0 50 50;\" xml:space=\"preserve\">
				        <style type=\"text/css\">
				        	.st0{fill:#FF355C;stroke:#000000;stroke-width:0.9063;stroke-miterlimit:10;}
				        </style>
				        <path class=\"st0\" d=\"M24.84,15.12c-1.08-1.76-0.33-4.05,0.78-5.79c1.53-2.4,3.73-4.42,6.39-5.5S37.8,2.68,40.4,3.9  c2.07,0.97,3.7,2.68,5.1,4.46c1.21,1.54,2.31,3.2,2.91,5.05c0.6,1.84,0.69,3.81,0.5,5.74c-0.17,1.73-0.56,3.46-1.36,5.01  c-1.16,2.23-3.1,3.98-5.16,5.45c-3.3,2.36-6.98,4.16-10.38,6.37c-3.4,2.21-6.61,4.96-8.36,8.57c-0.12-4.19-2.29-8.28-5.72-10.78  c-3.39-2.47-7.76-3.38-11.14-5.87c-5.08-3.75-7-10.89-5.04-16.82c0.73-2.22,2.01-4.34,3.98-5.65c2.35-1.56,5.46-1.75,8.19-0.99  s5.14,2.38,7.28,4.21c1.68,1.44,3.28,3.1,4,5.17L24.84,15.12z\"/>
				        </svg>
				      </div>

				      <!--USER2--><img src=\"{$this->user2_profile_img}\" style=\"margin-top:5px;max-width:150px;max-height:150px;\">

				      <!--USERNAME1--><p>{$this->user1_username}</p>
				      <p></p>
				      <!--USERNAME2--><p>{$this->user2_username}</p>
				    </div>

					</td>
        </tr>

				<!--End of 1st Row-->

        <tr>

          <td><!--Player Stats-->
						<div style='display: grid; justify-items: center;' id='m_stats'>
							<p>Regen Boost: {$this->m_stats['regen boost']}</p>
				    </div>
					</td>

          <td><!--Marriage Stats-->
						<div style='display: grid; justify-items: center;' id='m_data'>
							<p>Marriage Date: {$this->m_data['marriage date']}</p>
							<!--<p>Weeks Married: </p>-->

							<div id='divorce'>
								<form method='POST' action=''>
									<input name='divorce' type='submit' value='Divorce'></input>
								</form>
							</div>

				    </div>
					</td>

        </tr>

				<!--End-->
      </table>
    </div>
    ";
  }
}

class Marriage_DatabaseInteraction{

	/**
	* @return Null|Array
	*/
	public static function query_getMarriageStatus($username){
		global $system;

		$proposal_to_username_query = $system->query("SELECT `isMarried` FROM `users`
							WHERE `user_name` = '{$username}'");
		$proposal_to_username_result = $system->db_fetch($proposal_to_username_query);

		return $proposal_to_username_result;
	}

	/**
	* Update [isMarried] value to 'true' = [1]
	*/
	public static function setMarriageStatusToTrue_username($username){
		global $system;
		$system->query("UPDATE `users` SET
			`isMarried` = 1
		WHERE `user_name` = '{$username}' LIMIT 1");
	}

	/**
	* Update [isMarried] value to 'true' = [1]
	*/
	public static function setMarriageStatusToTrue_userId($id){
		global $system;
		$system->query("UPDATE `users` SET
			`isMarried` = 1
		WHERE `user_id` = '{$id}' LIMIT 1");
	}

	/**
	* Update [marriage_id] value using Username
	*/
	public static function setMarriageId_username($id, $username){
		global $system;
		$system->query("UPDATE `users` SET
			`marriage_id` = '{$id}'
		WHERE `user_name` = '{$username}' LIMIT 1");
	}

	/**
	* Update [marriage_id] value using User_Id
	*/
	public static function setMarriageId_userId($id, $user_id){
		global $system;
		$system->query("UPDATE `users` SET
			`marriage_id` = '{$id}'
		WHERE `user_id` = '{$user_id}' LIMIT 1");
	}

	/**
	* Get User Id's from Marriage Table
	*
	* Return Array[id1, id2]
	*/
	public static function getMarriageIDsfromMarriageTable($marriageId){
		global $system;
		$ids_query = $system->query("SELECT `user1_id`, `user2_id` FROM `marriage`
							WHERE `marriage_id` = '{$marriageId}'");
		$ids_result = $system->db_fetch($ids_query);

		$temp_arr = array($ids_result['user1_id'], $ids_result['user2_id']);
		return $temp_arr;
	}

	/**
	* Erase Marriage
	*/

	//TODO: isMarried isn't being set to 0 after divorce
	public static function divorce($id1, $id2){
		global $system;
		global $player;
		$system->query("UPDATE `users` SET
			`isMarried` = 0
		WHERE `user_id` = '{$id1}' LIMIT 1");

		$system->query("UPDATE `users` SET
			`isMarried` = 0
		WHERE `user_id` = '{$id2}' LIMIT 1");

		$system->query("UPDATE `users` SET
			`marriage_id` = '0'
		WHERE `user_id` = '{$id1}' LIMIT 1");

		$system->query("UPDATE `users` SET
			`marriage_id` = '0'
		WHERE `user_id` = '{$id2}' LIMIT 1");

		$system->query("UPDATE `users` SET
			`spouse_username` = ''
		WHERE `user_id` = '{$id1}' LIMIT 1");

		$system->query("UPDATE `users` SET
			`spouse_username` = ''
		WHERE `user_id` = '{$id2}' LIMIT 1");


		$player->marriageId = 0;
		$player->isMarried = 0;
		$player->spouse_username = '';
	}
}

/**
* Class is used to display a username search box, or the yes/no box for sending proposalInbox
* the class also handles db check for a username returns Null or
* @param Bool $user1CurrentMarriageStatus  | If User is Married themself T/F
* @param String|Null $proposed_name | User input, proposal name
* @param Bool|Null $user2_isMarried | Proposal person is married T/F
*
* @return String|Null if username of proposee exists function will return a string
*/
class MarriageModel{

	private bool $user1CurrentMarriageStatus = false; //main user
	private ?string $proposed_name = Null;
	private ?bool $user2_isMarried = True; //Want to Marry (lets just assume they are married already as a fail safe)

	private string $proposedfrom = '';

	//constructors
	public function __construct($proposed_name){
		$this->proposed_name = $proposed_name;
	}

	public function __createProposalFromInbox($proposedFrom){
		$this->displayAcceptProposalBox_yes_no($proposedFrom);
	}

	function checkDB_setProposalName($temp_name){

		global $player;

		//might cause issue if they've sent a proposal to an inactive player
		// TODO: set up a way for users to send another proposal after 24 hours lol
		if($player->marriageId == -1){
			global $system;
			//check if proposals were made before
			$proposalsQuery = $system->query("SELECT `proposalInbox` FROM `users`
								WHERE `proposalInbox` = '{$player->getName()}'");
			// $proposalsResult = $system->db_fetch($proposalsQuery);
			//if no proposals were made (this can happen when proposal is made but then erased its a temp fix)
			//users will have to refresh twice if this is the case, needs to be patched
			if(mysqli_num_rows($proposalsQuery) == 0){
				$system->query("UPDATE `users` SET
					`marriage_id` = '0'
				WHERE `user_id` = '{$player->user_id}' LIMIT 1");
			}

			$this->msg("You've made a proposal already, please wait for a response or 24 hours.");
		}

		if($this->proposed_name == Null){
			return Null;
		}

		if($this->proposed_name !== Null){

			if($this->checkDB_forUserExistance($this->proposed_name)) return true;

			if($this->user2_isMarried == false){
				$this->msg("They're available!");
				$this->displayProposalOptions_Yes_No();
				return $temp_name;
			}
		}

		if($this->user2_isMarried == true){
		  $this->msg("{$this->proposed_name} is already married or doesn't exist!");
			return Null;
		}

	  $this->msg("{$this->proposed_name} does not exist...");
		return Null;
	}

	/*
	* return false <-- this is actually really bad I should fix this...
	*/
	private function checkDB_forUserExistance($proposal_to_username){

		//*NEW DB CLASS HERE*//
		$proposal_to_username_result = Marriage_DatabaseInteraction::query_getMarriageStatus($proposal_to_username);

		if(isset($proposal_to_username_result)){
			// $system->message("Checking the files");
			$this->user2_isMarried = $proposal_to_username_result['isMarried'];
			return false;
		}
		else {
			$this->msg("They Don't Exist!");
			return false;
		}
	}

	public static function msg($msg){
		global $system;
		$system->message($msg);
		$system->printMessage();
	}

	//default display
	function displaySearchBox(){
		$headerName = "Marriage Department";
		echo "
		<table class='table'>
			<th colspan='1'>{$headerName}</th>
			<tr><!--Display Area-->
				<td style='display: grid; justify-content: center; align-items: center;'><!--Marriage Forms-->
					<form method='POST' action='' style='padding: 2em 0em;'>
						<label for='m_name'>Propose: </label>
						<input id='m_name' type='text' name='proposal_name' minlength='2' maxlength='16' autofocus autocomplete='off' required>
						<input type='submit' name='submit'>
					</form>
				</td>
			</tr>
		</table>


		";
	}


	//if the proposal can be sent (option to send a proposal)
	function displayProposalOptions_Yes_No(){
		$headerName = "Proposal!";

		echo "
		<table class='table'>
			<th colspan='1'>{$headerName}</th>
			<tr><!--Display Area-->
				<td style='display: grid; justify-items: center;'><!--Marriage Forms-->
					<label for='proposal_option'>Would you like to propose to {$this->proposed_name}?</label>
					<form id='proposal_option' method='POST' action='' style='padding: 2em 0em;'>
						<label for='prop_yes'>Yes</label>
						<input id='prop_yes' type='radio' name='marriage_radio_btn' value='{$this->proposed_name}' checked>
						<label for='prop_no'>No</label>
						<input id='prop_no' type='radio' name='marriage_radio_btn' value='no'>
						<input style='' type='submit' name='submit' autofocus>
					</form>
				</td>
			</tr>
		</table>
		";
	}

	function displayAcceptProposalBox_yes_no($proposalFrom){
		$headerName = "Do you Accept?";

		echo "
		<table class='table'>
			<th colspan='1'>{$headerName}</th>
			<tr><!--Display Area-->
				<td style='display: grid; justify-items: center;'><!--Marriage Forms-->
					<label for='proposal_option'>Would you like to accept a proposal from {$proposalFrom}?</label>
					<form id='proposal_option' method='POST' action='' style='padding: 2em 0em;'>
						<label for='prop_yes'>Yes</label>
						<input id='prop_yes' type='radio' name='accept_proposal' value='{$proposalFrom}' checked>
						<label for='prop_no'>No</label>
						<input id='prop_no' type='radio' name='accept_proposal' value='no'>
						<input style='' type='submit' name='submit' autofocus>
					</form>
				</td>
			</tr>
		</table>
		";
	}
}

?>
