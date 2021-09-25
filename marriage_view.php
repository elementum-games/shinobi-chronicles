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
		$view = new MarriageView(/*id in here or something*/);
		$view->display();
	} else {

		//if page reloaded with Option 'yes' to propose
		if(isset($_POST['marriage_radio_btn'])){
			$m_temp = $system->clean($_POST['marriage_radio_btn']);
			$system->message($m_temp);
			$system->printMessage();
		}

		//if page reloaded with proposal name
		$temp_proposal_name = Null;
		if(isset($_POST['proposal_name'])){
			$temp_proposal_name = $system->clean($_POST['proposal_name']);

			if(strtolower($temp_proposal_name) == strtolower($player->user_name)) {
				$system->message("You can't marry yourself...");
				$temp_proposal_name = Null;
			}
		}

		$marriageDepartment = new MarriageDepartment($temp_proposal_name);
		$temp_proposal_name = $marriageDepartment->runCheck($temp_proposal_name);

		if($temp_proposal_name == Null) $marriageDepartment->displaySearchBox();
		//display stuff if married or not

		//display proposal portion of page
		$system->printMessage();
	}

	// echo "What";
}

class MarriageView{

  public $tablename = 'Marriage Panel';

	private $m_stats = array(
		'regen boost' => 10,

	);
	private $m_data = array(
		'marriage date' => '9/23/2021',
		'weeks married' => '2'
	);

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

class MarriageDepartment{

	private bool $user1CurrentMarriageStatus = false; //main user
	private ?string $proposed_name = Null;
	private ?bool $user2_isMarried = True; //Want to Marry (lets just assume they are)

	//constructors
	public function __construct($proposed_name){
		$this->proposed_name = $proposed_name;
	}

	function runCheck($temp_name){

		global $system;

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
						<input id='prop_no' type='radio' name='marriage_radio_btn'>
						<input style='' type='submit' name='submit' autofocus>
					</form>
				</td>
			</tr>
		</table>
		";
	}
}

?>
