<?php
session_start();

require("variables.php");

switch($DEFAULT_LAYOUT) {
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
echo str_replace("[HEADER_TITLE]", "Rules", $body_start);

?>

<table class='table'><tr><th>Manual</th></tr>
    <tr><td>


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

echo str_replace('<!--[VERSION_NUMBER]-->', $VERSION_NUMBER, $footer);
