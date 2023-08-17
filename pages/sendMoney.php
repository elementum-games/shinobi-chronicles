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
                throw new RuntimeException("You cannot send currency to yourself!");
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
                if($amount > $player->currency->getMoney()) {
                    throw new RuntimeException("You do not have that much {$player->money->name}!");
                }
                $player->currency->subtractMoney($amount, "Sent money to {$recipient->user_name} (#{$recipient->user_id})");
                $recipient->currency->addMoney($amount, "Received money from $player->user_name (#$player->user_id)", false);

                // Player will be auto-updated later
                $recipient->updateData();

                $system->log(
                    'money_transfer',
                    'Money Sent',
                    "{$amount} " . Currency::MONEY_NAME . " - #{$player->user_id} ($player->user_name) to #{$recipient->user_id}"
                );

                $alert_message = $player->user_name . " has sent you " . Currency::MONEY_SYMBOL . "$amount.";
                Inbox::sendAlert($system, Inbox::ALERT_YEN_RECEIVED, $player->user_id, $recipient->user_id, $alert_message);

                $system->message(Currency::MONEY_SYMBOL . "{$amount} sent to {$recipient->user_name}!");

            }
            if($currency_type == System::CURRENCY_TYPE_PREMIUM_CREDITS) {
                if($amount > $player->currency->getPremiumCredits()) {
                    throw new RuntimeException("You do not have that much {$player->currency->premium_credits->name}!");
                }
                $player->currency->subtractPremiumCredits($amount, "Sent {$player->currency->premium_credits->name} to {$recipient->user_name} (#{$recipient->user_id})");
                $recipient->currency->addPremiumCredits($amount, "Received {$player->currency->premium_credits->name} from $player->user_name (#$player->user_id)");

                // Player will be auto-updated later
                $recipient->updateData();

                $system->log(
                    'premium_credit_transfer',
                    'Premium Credits Sent',
                    "{$amount} {$player->currency->premium_credits->symbol} - #{$player->user_id} ($player->user_name) to #{$recipient->user_id}"
                );

                $alert_message = $player->user_name . " has sent you $amount " . Currency::PREMIUM_NAME . ".";
                Inbox::sendAlert($system, Inbox::ALERT_AK_RECEIVED, $player->user_id, $recipient->user_id, $alert_message);

                $system->message("{$amount} {$player->currency->premium_credits->name} sent to {$recipient->user_name}!");
            }

        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }

    $recipient = $_GET['recipient'] ?? '';
    
    if (isset($_GET['recipient'])) {
        echo "<table class='table' style='width: 125px''><tr><td style='text-align: center'><a style='tab-index: 0' href='" . $system->router->getUrl("members",["user" => $recipient]) . "'>Back to Profile</a></td></tr></table>";
    }

    echo "<table class='table'><tr><th>Send {$player->currency->money->name}</th><th>Send {$player->currency->premium_credits->name}</th></tr>
    <tr><td style='text-align:center;'>
    <form action='{$system->router->links['send_money']}&currency=yen' method='post'>
    <b>Your Money:</b> {$player->currency->getFormattedMoney()}<br />
    <br />
    Send {$player->currency->money->name} to:<br />
    <input type='text' name='recipient' value='{$recipient}' /><br />
    Amount:<br />
    <input type='text' name='amount' /><br />
    <input type='submit' name='send_currency' value='Send {$player->currency->money->name}' />
    </form></td>
    <td style='text-align:center;'>
    <form action='{$system->router->links['send_money']}&currency=ak' method='post'>
    <b>Your {$player->currency->premium_credits->symbol}:</b> {$player->currency->getFormattedPremiumCredits()}<br />
    <br />
    Send {$player->currency->premium_credits->symbol} to:<br />
    <input type='text' name='recipient' value='{$recipient}' /><br />
    Amount:<br />
    <input type='text' name='amount' /><br />
    <input type='submit' name='send_currency' value='Send {$player->currency->premium_credits->symbol}' />
    </form></td>
    </tr></table>";
}