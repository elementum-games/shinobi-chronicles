<?php
if(!isset($_SESSION['user_id'])) {
	exit;
}

//fires when user first enters page
function marriage_controller(){

  global $player;

  if(!$player->isMarred){
    header("Location: http://localhost/shinobi-chronicles/?id=1"); //change ethis before push
    die();
  }

  $view = new MarriageView();
  $view->display();
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



?>
