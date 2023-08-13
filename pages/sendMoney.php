<?php

/**
 * @throws RuntimeException
 */
function sendMoney() {
    global $system;
    global $player;

    $currency_type = "";

    if(isset($_POST['send_currency'])) {
        if(isset($_GET['currency'])) {
            if($_GET['currency'] == 'yen') {
                $currency_type = System::CURRENCY_TYPE_MONEY;
            }
            else if($_GET['currency'] == 'ak') {
                $currency_type = System::CURRENCY_TYPE_PREMIUM_CREDITS;
            }
        }

        if ($currency_type != System::CURRENCY_TYPE_MONEY && $currency_type != System::CURRENCY_TYPE_PREMIUM_CREDITS) {
            throw new RuntimeException("Invalid currency type!");
        }

        $recipient = $system->db->clean($_POST['recipient']);
        $amount = (int)$_POST['amount'];

        try {
            if(strtolower($recipient) == strtolower($player->user_name)) {
                throw new RuntimeException("You cannot send money/AK to yourself!");
            }
            if($amount <= 0 && !$player->isHeadAdmin()) {
                throw new RuntimeException("Invalid amount!");
            }

            $recipient = User::findByName($system, $recipient);
            if(!$recipient) {
                throw new RuntimeException("Invalid user!");
            }

            $recipient->loadData(User::UPDATE_NOTHING, true);

            if($currency_type == System::CURRENCY_TYPE_MONEY) {
                if($amount > $player->money->getAmount()) {
                    throw new RuntimeException("You do not have that much money/AK!");
                }
                $player->money->subtract($amount, "Sent money to {$recipient->user_name} (#{$recipient->user_id})");
                $recipient->money->add($amount, "Received money from $player->user_name (#$player->user_id)", false);

                // Player will be auto-updated later
                $recipient->updateData();

                $system->log(
                    'money_transfer',
                    'Money Sent',
                    "{$amount} yen - #{$player->user_id} ($player->user_name) to #{$recipient->user_id}"
                );

                $alert_message = $player->user_name . " has sent you &yen;$amount.";
                Inbox::sendAlert($system, Inbox::ALERT_YEN_RECEIVED, $player->user_id, $recipient->user_id, $alert_message);

                $system->message("&yen;{$amount} sent to {$recipient->user_name}!");

            }
            else if($currency_type == System::CURRENCY_TYPE_PREMIUM_CREDITS) {
                if($amount > $player->getPremiumCredits()) {
                    throw new RuntimeException("You do not have that much AK!");
                }
                $player->subtractPremiumCredits($amount, "Sent AK to {$recipient->user_name} (#{$recipient->user_id})");
                $recipient->addPremiumCredits($amount, "Received AK from $player->user_name (#$player->user_id)");

                // Player will be auto-updated later
                $recipient->updateData();

                $system->log(
                    'premium_credit_transfer',
                    'Premium Credits Sent',
                    "{$amount} AK - #{$player->user_id} ($player->user_name) to #{$recipient->user_id}"
                );

                $alert_message = $player->user_name . " has sent you $amount Ancient Kunai.";
                Inbox::sendAlert($system, Inbox::ALERT_AK_RECEIVED, $player->user_id, $recipient->user_id, $alert_message);

                $system->message("{$amount} AK sent to {$recipient->user_name}!");
            }

        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }

    $current_amount_money = $player->money->getSymbol() . $player->money->getAmount();
    $current_amount_ak = $player->getPremiumCredits();

    $recipient = $_GET['recipient'] ?? '';
    
    if (isset($_GET['recipient'])) {
        echo "<table class='table' style='width: 125px''><tr><td style='text-align: center'><a style='tab-index: 0' href='" . $system->router->getUrl("members",["user" => $recipient]) . "'>Back to Profile</a></td></tr></table>";
    }

    echo "<table class='table'><tr><th>Send Money</th><th>Send AK</th></tr>
    <tr><td style='text-align:center;'>
    <form action='{$system->router->links['send_money']}&currency=yen' method='post'>
    <b>Your Money:</b> {$current_amount_money}<br />
    <br />
    Send Money to:<br />
    <input type='text' name='recipient' value='{$recipient}' /><br />
    Amount:<br />
    <input type='text' name='amount' /><br />
    <input type='submit' name='send_currency' value='Send Money' />
    </form></td>
    <td style='text-align:center;'>
    <form action='{$system->router->links['send_money']}&currency=ak' method='post'>
    <b>Your AK:</b> {$current_amount_ak}<br />
    <br />
    Send AK to:<br />
    <input type='text' name='recipient' value='{$recipient}' /><br />
    Amount:<br />
    <input type='text' name='amount' /><br />
    <input type='submit' name='send_currency' value='Send AK' />
    </form></td>
    </tr></table>";
}