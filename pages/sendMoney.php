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

            $result = $system->db->query(
                "SELECT `user_id`, `user_name`, `money`, `premium_credits`
                        FROM `users`
                        WHERE `user_name`='$recipient' LIMIT 1"
            );
            if(!$system->db->last_num_rows) {
                throw new RuntimeException("Invalid user!");
            }
            $recipient = $system->db->fetch($result);

            if($currency_type == System::CURRENCY_TYPE_MONEY) {
                if($amount > $player->getMoney()) {
                    throw new RuntimeException("You do not have that much money/AK!");
                }
                $player->subtractMoney($amount, "Sent money to {$recipient['user_name']} (#{$recipient['user_id']})");

                $system->db->query(
                    "UPDATE `users` SET `money`=`money` + $amount WHERE `user_id`='{$recipient['user_id']}' LIMIT 1"
                );
                $system->currencyLog(
                    $recipient['user_id'],
                    $currency_type,
                    $recipient['money'],
                    $recipient['money'] + $amount,
                    $amount,
                    "Received money from $player->user_name (#$player->user_id)"
                );

                $system->log(
                    'money_transfer',
                    'Money Sent',
                    "{$amount} yen - #{$player->user_id} ($player->user_name) to #{$recipient['user_id']}"
                );

                $alert_message = $player->user_name . " has sent you &yen;$amount.";
                Inbox::sendAlert($system, Inbox::ALERT_YEN_RECEIVED, $player->user_id, $recipient['user_id'], $alert_message);

                $system->message("&yen;{$amount} sent to {$recipient['user_name']}!");

            }
            else if($currency_type == System::CURRENCY_TYPE_PREMIUM_CREDITS) {
                if($amount > $player->getPremiumCredits()) {
                    throw new RuntimeException("You do not have that much AK!");
                }
                $player->subtractPremiumCredits($amount, "Sent AK to {$recipient['user_name']} (#{$recipient['user_id']})");

                $system->db->query(
                    "UPDATE `users` SET `premium_credits`=`premium_credits` + $amount WHERE `user_id`='{$recipient['user_id']}' LIMIT 1"
                );
                $system->currencyLog(
                    $recipient['user_id'],
                    $currency_type,
                    $recipient['premium_credits'],
                    $recipient['premium_credits'] + $amount,
                    $amount,
                    "Received AK from $player->user_name (#$player->user_id)"
                );

                $system->log(
                    'premium_credit_transfer',
                    'Premium Credits Sent',
                    "{$amount} AK - #{$player->user_id} ($player->user_name) to #{$recipient['user_id']}"
                );

                $alert_message = $player->user_name . " has sent you $amount Ancient Kunai.";
                Inbox::sendAlert($system, Inbox::ALERT_AK_RECEIVED, $player->user_id, $recipient['user_id'], $alert_message);

                $system->message("{$amount} AK sent to {$recipient['user_name']}!");
            }

        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }

    $current_amount_money = "&yen;" . $player->getMoney();
    $current_amount_ak = $player->getPremiumCredits();

    $recipient = $_GET['recipient'] ?? '';
    
    if (isset($_GET['recipient'])) {
        echo "<table class='table' style='width: 75px''><tr><td style='text-align: center'><a style='tab-index: 0' href='" . $system->router->getUrl("members",["user" => $recipient]) . "'>Return</a></td></tr></table>";
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