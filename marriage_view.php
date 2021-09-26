<?php
if(!isset($_SESSION['user_id'])) {
	exit;
}


//fires when user first enters page
function marriage_controller(){

	global $player;
	global $system;


	if($player->isMarried){
		//find marriage ID
		//if ID invalid -> break
		$view = new MarriageView($player->marriedId);
		$view->display();
	} else {

		//Answer to Proposal
		if(isset($_POST['accept_proposal'])){
			$proposal_answer = $system->clean($_POST['accept_proposal']);


			if($proposal_answer != 'no'){
				//erase proposalInbox from current user and other users
				//create marriage ID assign to both parties and then reload page

				//set users to married
				$system->query("UPDATE `users` SET
					`isMarried` = '1'
				WHERE `user_name` = '{$proposal_answer}' LIMIT 1");
				$system->query("UPDATE `users` SET
					`isMarried` = '1'
				WHERE `user_id` = '{$player->user_id}' LIMIT 1");

				//erase proposal proposalInbox
				$system->query("UPDATE `users` SET
					`marriage_id` = '0'
				WHERE `user_name` = '{$proposal_answer}' LIMIT 1");

				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `user_name` = '{$player->getName()}' LIMIT 1");

				//create new MarriageId Row in MarriageData database and set the VALUES


				$system->message("Woah you're married now!");
				$system->printMessage();

				header("Refresh:0"); //don't know if this is okay...
				return; //reload page
			} else {
				//erase proposal inbox from current user and then reset other user marriageId to 0 so they can propose again
				$system->query("UPDATE `users` SET
					`marriage_id` = '0'
				WHERE `user_name` = '{$proposal_answer}' LIMIT 1");

				$system->query("UPDATE `users` SET
					`proposalInbox` = ''
				WHERE `user_name` = '{$player->getName()}' LIMIT 1");

				$system->message("You rejected them");
				$system->printMessage();
			}
		}

		//query
		//if username exists in the proposalinbox -> display [accept yes/no]
		$proposalInbox = $system->query("SELECT `proposalInbox` FROM `users`
							WHERE `user_id` = '{$player->user_id}'");
		$proposalInbox = $system->db_fetch($proposalInbox);
		if($proposalInbox['proposalInbox'] != ''){

			$marriageDepartment = new MarriageDepartment(Null);
			$marriageDepartment->displayAcceptProposalBox_yes_no($proposalInbox['proposalInbox']);
		}


		//if page reloaded with Option 'yes' to propose
		if(isset($_POST['marriage_radio_btn'])){
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

				$system->message("Proposal Sent!");
				$system->printMessage();
			} else {
				$system->message("{$m_temp} already has a proposal :(!");
				$system->printMessage();
			}
		}

		//if page reloaded with proposal name
		$temp_proposal_name = Null;
		if(isset($_POST['proposal_name'])){
			$temp_proposal_name = $system->clean($_POST['proposal_name']);

			if(strtolower($temp_proposal_name) == strtolower($player->getName())) {
				$system->message("You can't marry yourself...");
				$temp_proposal_name = Null;
			}
		}


		$marriageDepartment = new MarriageDepartment($temp_proposal_name);
		$temp_proposal_name = $marriageDepartment->runCheck($temp_proposal_name);

		if($temp_proposal_name == Null) $marriageDepartment->displaySearchBox();
		//display stuff if married or not

		// //display proposal portion of page
		// $system->printMessage();
	}

	// echo "What";
}

/**
* @param String $tablename displays title of the html table
* @param Array $m_stats holds marriage stats i.e boosts
* @param Array $m_data holds marriage 'info' i.e dates/number
*/
class MarriageView{

  public $tablename = 'Marriage Panel';

	private $m_stats = array(
		'regen boost' => 10,

	);
	private $m_data = array(
		'marriage date' => '9/23/2021',
		'weeks married' => '2'
	);

	function __construct($marriageId){

	}

  function display(){
    echo "
    <div>
      <table class='table'>
      <th colspan='2'>{$this->tablename}</th>

        <tr><!--Display Area-->
          <td colspan='2'>{$this->niceview}</td>
        </tr>

        <tr>

          <td><!--Player Stats-->
						<div style='display: grid; justify-items: center;' id='m_stats'>
							<p>Regen Boost: {$this->m_stats['regen boost']}</p>
				    </div>
					</td>

          <td><!--Marriage Stats-->
						<div style='display: grid; justify-items: center;' id='m_data'>
							<p>Marriage Date: {$this->m_data['marriage date']}</p>
							<p>Weeks Married: {$this->m_data['weeks married']}</p>
				    </div>
					</td>

        </tr>
      </table>
    </div>
    ";
  }

  //gonna have to move this to the display() up above later
  public $niceview =
    "
    <div id='niceview' style='display: grid; grid-template-columns: 1fr 0.3fr 1fr; justify-items: center; align-items: center; padding: 1em 0.5em;'>
      <img src=\"https://i.imgur.com/g1SRylX.gif\" style=\"margin-top:5px;max-width:150px;max-height:150px;\">

      <div style='width: 4em'>
				<!--I drew this<3-->
        <svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" version=\"1.1\" id=\"Layer_1\" x=\"0px\" y=\"0px\" viewBox=\"0 0 50 50\" style=\"enable-background:new 0 0 50 50;\" xml:space=\"preserve\">
        <style type=\"text/css\">
        	.st0{fill:#FF355C;stroke:#000000;stroke-width:0.9063;stroke-miterlimit:10;}
        </style>
        <path class=\"st0\" d=\"M24.84,15.12c-1.08-1.76-0.33-4.05,0.78-5.79c1.53-2.4,3.73-4.42,6.39-5.5S37.8,2.68,40.4,3.9  c2.07,0.97,3.7,2.68,5.1,4.46c1.21,1.54,2.31,3.2,2.91,5.05c0.6,1.84,0.69,3.81,0.5,5.74c-0.17,1.73-0.56,3.46-1.36,5.01  c-1.16,2.23-3.1,3.98-5.16,5.45c-3.3,2.36-6.98,4.16-10.38,6.37c-3.4,2.21-6.61,4.96-8.36,8.57c-0.12-4.19-2.29-8.28-5.72-10.78  c-3.39-2.47-7.76-3.38-11.14-5.87c-5.08-3.75-7-10.89-5.04-16.82c0.73-2.22,2.01-4.34,3.98-5.65c2.35-1.56,5.46-1.75,8.19-0.99  s5.14,2.38,7.28,4.21c1.68,1.44,3.28,3.1,4,5.17L24.84,15.12z\"/>
        </svg>
      </div>

      <img src=\"https://i.imgur.com/g1SRylX.gif\" style=\"margin-top:5px;max-width:150px;max-height:150px;\">

      <!--Thank you grid<3-->
      <p>Cextra</p>
      <p></p>
      <p>Cextra2</p>
    </div>
    "
    ;
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
class MarriageDepartment{

	private bool $user1CurrentMarriageStatus = false; //main user
	private ?string $proposed_name = Null;
	private ?bool $user2_isMarried = True; //Want to Marry (lets just assume they are)

	private string $proposedfrom = '';

	//constructors
	public function __construct($proposed_name){
		$this->proposed_name = $proposed_name;
	}
	public function __createProposalFromInbox($proposedFrom){
		$this->displayAcceptProposalBox_yes_no($proposedFrom);
	}

	function runCheck($temp_name){

		global $system;
		global $player;

		//makes sure current user hasn't already sent a proposalInbox
		//might cause issue if they've sent a proposal to an inactive player
		//set up a way for users to send another proposal after 24 hours lol
		if($player->marriageId == -1){
			$system->message("You've made a proposal already, please wait for a response or 24 hours.");
		}

		if($this->proposed_name == Null){
			return Null;
		}

		if($this->proposed_name !== Null){

			if($this->checkDB($this->proposed_name)) return true;

			if($this->user2_isMarried == false){
				$system->message("They're available!");
				$this->displayProposalOptions_Yes_No();
				return $temp_name;
			}
		}

		if($this->user2_isMarried == true){
		  $system->message("{$this->proposed_name} is already married or doesn't exist!");
			return Null;
		}

	  $system->message("{$this->proposed_name} does not exist...");
		return Null;
	}

	/*
	* return array || false
	*/
	function checkDB($proposal_to_username){
		global $system;
		$proposal_to_username_query = $system->query("SELECT `isMarried` FROM `users`
							WHERE `user_name` = '{$proposal_to_username}'");

		$proposal_to_username_result = $system->db_fetch($proposal_to_username_query);

		if(isset($proposal_to_username_result)){
			// $system->message("Checking the files");
			$this->user2_isMarried = $proposal_to_username_result['isMarried'];
		} else {
			// $system->message("They don't exist...");
		}
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
						<input id='prop_no' type='radio' name='marriage_radio_btn' value=''>
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
