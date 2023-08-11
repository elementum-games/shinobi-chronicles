<?php
require '_authenticate_admin.php';
$RANKS = RankManager::fetchNames($system);
$yen_users = [];
class YenUser extends User {
    function __construct(public System $system, public int $rank_num) {
        $this->system = $system;
        $this->rank_num = $rank_num;
    }
}

foreach($RANKS as $RANK_ID => $RANK) {
    $yen_users[] = new YenUser($system, $RANK_ID);
}

foreach($yen_users as $yen_user) {
    echo "<div style='display:inline-block;width:25%;vertical-align:text-top;'>";
    foreach([5, 10, 25, 100] as $x) {
        echo "<b>Rank $yen_user->rank_num - <em>Multiple of $x</em></b><br />";
        for($i=1;$i<=10;$i++) {
            echo "Multiplier $i: &yen;" . $yen_user->calcPlayerMoneyGain($i, $x) . "<br />";
        }
        echo "<br />";
    }
    echo "</div>";
}