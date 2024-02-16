<?php
function premiumShop() {
    global $system;
    global $player;
    global $self_link;

    // Set landing page
    $landing_page = 'purchase_ak';
    if($player->getPremiumCredits()) {
        $landing_page = 'character_changes';
    }

    require 'templates/premium/premium_v2.php';
}