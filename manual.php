<?php
session_start();

require "classes/system.php";

switch(SystemFunctions::DEFAULT_LAYOUT) {
    case 'classic_blue':
        require("layout/classic_blue.php");
        break;
    case 'shadow_ribbon':
        require("layout/shadow_ribbon.php");
        break;
    default:
        require("layout/classic_blue.php");
        break;
}

echo $heading;
echo $top_menu;
echo $header;
echo str_replace("[HEADER_TITLE]", "Manual", $body_start);

?>

<table class='table'><tr><th>Manual</th></tr>
    <tr><td>
        <b>Contributing</b>
        To submit changes to this manual (you may need a GitHub account):
        <ol>
            <li>Go to <a href='https://github.com/levimeahan/shinobi-chronicles/blob/main/manual.php'>manual.php on GitHub</a></li>
            <li>Click the pencil icon on top right</li>
            <li>Make your changes</li>
            <li>Scroll down and click "Propose Changes"</li>
            <li>Click "Create Pull Request"</li>
        </ol>

        <!-- Content -->
        <div>

        </div>
    </td></tr>
</table>

<?php
if(isset($_SESSION['user_id'])) {
    echo $side_menu_start;
    echo $side_menu_end;
}
else {
    echo $login_menu;
}

echo str_replace('<!--[VERSION_NUMBER]-->', SystemFunctions::VERSION_NUMBER, $footer);
