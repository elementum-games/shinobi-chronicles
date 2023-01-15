<?php
if(!$_POST) {
	exit;
}

require("classes.php");
$system = new System();
$system->dbConnect();


// Read the notification from PayPal and create the acknowledgement response
$req = 'cmd=_notify-validate';               // add 'cmd' to beginning of the acknowledgement you send back to PayPal

foreach ($_POST as $key => $value) {         // Loop through the notification NV pairs
	$value = urlencode(stripslashes($value));  // Encode the values
	$req .= "&$key=$value";                    // Add the NV pairs to the acknowledgement
}

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// post back to PayPal system to validate

//post back to PayPal system to validate (replaces old headers)
$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Host: www.paypal.com\r\n";
$header .= "Connection: close\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
$txn_id =	 		$system->clean($_POST['txn_id']);
$payment_date = 	$system->clean($_POST['payment_date']);
$time = 			time();
$user_id =			$system->clean($_POST['custom']);
$buyer_name = 		$system->clean($_POST['first_name'] . ' ' . $_POST['last_name']);
$buyer_email = 		$system->clean($_POST['payer_email']);
$payment_amount = 	$system->clean($_POST['mc_gross']);
$quantity = 		$system->clean($_POST['quantity']);
$payment_currency = $system->clean($_POST['mc_currency']);
$address_city =		$system->clean($_POST['address_city']);
$address_country =	$system->clean($_POST['address_country']);
$address_state =	$system->clean($_POST['address_state']);
$address_street =	$system->clean($_POST['address_street']);
$address_zip =		$system->clean($_POST['address_zip']);
$address_status =	$system->clean($_POST['address_status']);

$receiver_email = 	$_POST['receiver_email'];
$payment_status = 	$_POST['payment_status'];

if (!$fp) {
	// HTTP ERROR
	$system->send_pm("IPN Listener", "Lsmjudoka", "IPN Received", "HTTP Error");
} 
else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);	
		$res = trim($res);
		if (strcmp($res, "VERIFIED") == 0) {
			$message = "
			User ID: $user_id
			Status: $payment_status 
			Amount: $payment_amount
			Currency: $payment_currency
			Id: $txn_id
			Recipient email: $receiver_email
			Sender email: $buyer_email";
			$system->send_pm("IPN Listener", "Lsmjudoka", "Payment", $message);
			try {
				// check the payment_status is Completed
				if($payment_status != "Completed") {
					throw new Exception("Payment is not complete.");
				}
				// check that txn_id has not been previously processed
				$result = $system->query("SELECT `id` FROM `Payments` WHERE `txn_id`='$txn_id' LIMIT 1");
				if($system->db_last_num_rows > 0 ) {
					throw new Exception("Payment has already been processed!");
				}
				
				// check that receiver_email is your Primary PayPal email
				if($receiver_email != "lsmjudoka05@yahoo.com" && $receiver_email != "Lsm_1276033742_biz@gmail.com") {
					throw new Exception("Invalid recipient!");
				}
				// check that payment_amount/payment_currency are correct
				if($payment_currency != "USD") {
					$system->send_pm("IPN Listener", "Lsmjudoka", "IPN Error", "Invalid currency -- #$txn_id");
					throw new Exception("Invalid currency!");
				}
				// Check payment amount
				$payment_amount = floor($payment_amount);
				if($payment_amount < 1) {
					throw new Exception("Invalid payment, < $1!");
				}

				$kunai_amount = $payment_amount * System::KUNAI_PER_DOLLAR;

				$query = "INSERT INTO `Payments` (`txn_id`, `payment_date`, `time`, `username`, `buyer_name`, `buyer_email`, `payment_amount`, `quantity`,
				`payment_currency`, `address_city`, `address_country`, `address_state`, `address_street`, `address_zip`, `address_status`) VALUES
				('$txn_id', '$payment_date', '$time', 'user:$user_id', '$buyer_name', '$buyer_email', '$payment_amount', '$quantity', 
				'$payment_currency', '$address_city', '$address_country', '$address_state', '$address_street', '$address_zip', '$address_status')";
				$system->query($query);
				
				// Check shard amount
				$bonus = 0;
				$kunai_packs = $system->getKunaiPacks();
				$selected_pack = null;
				foreach($kunai_packs as $pack) {
				    if($payment_amount < $pack['cost']) {
                        break;
                    }

				    $selected_pack = $pack;
                }

				$bonus = $selected_pack['bonus'] ?? 0;

                $total_amount = $kunai_amount + $bonus;

				$query = "UPDATE `users` SET 
				`premium_credits` = `premium_credits` + '" . ($total_amount) . "', 
				`premium_credits_purchased` = `premium_credits_purchased` + '" . ($total_amount) . "'
				WHERE `user_id`='$user_id' LIMIT 1";
				$system->query($query);

                $result = $system->query("SELECT `premium_credits` FROM `users` WHERE `user_id`='{$user_id}'");
                $user = $system->db_fetch($result);

                if($user == null) {
                    $system->log('shard_purchase', 'invalid_user',"Transaction $txn_id for user #{$user_id}");
                }
                else {
                    $system->currencyLog(
                        $user_id,
                        System::CURRENCY_TYPE_PREMIUM_CREDITS,
                        $user['premium_credits'] - $total_amount,
                        $user['premium_credits'],
                        $total_amount,
                        "Purchased AK via Paypal (TXN: $txn_id)"
                    );
                }

				$system->send_pm("Lsmjudoka", "$user_id", "Shard purchase",
				"Your purchase of " . ($kunai_amount + $bonus) . " Ancient Kunai" . ($bonus > 0 ? " ($kunai_amount + $bonus bonus)" : "") .
				" has been processed and credited to your account. Thank you!");
				
				// process payment
			} catch(Exception $e) {
				$system->send_pm("IPN Listener", "Lsmjudoka", "IPN error", $e->getMessage() . "\r\nID: $txn_id");
			}
		}
		else if (strcmp ($res, "INVALID") == 0) {
			// log for manual investigation
			$system->send_pm("IPN Listener", "Lsmjudoka", "IPN Received", "Invalid!" .
			$_SERVER['REMOTE_ADDR'] . "<br />" . json_encode($_POST));
		}
		else {
			// $system->send_pm("IPN Listener", "Lsmjudoka", "IPN Received", "Invalid! '$res'<br />" . json_encode($_POST));	
		}
	}
	fclose ($fp);
}
