<?php
// Read POST data from PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode('=', $keyval);
  if (count($keyval) == 2) {
    $myPost[$keyval[0]] = urldecode($keyval[1]);
  }
}

// Build the request to validate with PayPal
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
  $value = urlencode($value);
  $req .= "&$key=$value";
}

// Send validation request back to PayPal
$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);
if (!$res) {
    error_log("IPN request failed: " . curl_error($ch));
    exit;
}
curl_close($ch);

// Check the response
if (strcmp($res, "VERIFIED") == 0) {
    // ✅ The IPN is legitimate
    // Extract needed data
    $payment_status = $_POST['payment_status'] ?? '';
    $txn_id = $_POST['txn_id'] ?? '';
    $receiver_email = $_POST['receiver_email'] ?? '';
    $payer_email = $_POST['payer_email'] ?? '';

    // Validate payment
    if ($payment_status == "Completed" && $receiver_email == "vchipz69@gmail.com") {
        // Generate a unique token for download
        $token = bin2hex(random_bytes(16));

        // Save token to file for validation, linked to txn_id
        if (!is_dir("tokens")) {
            mkdir("tokens", 0755, true);
        }
        file_put_contents("tokens/$txn_id.txt", $token);

        // Log the successful payment
        file_put_contents("ipn_log.txt", "Payment verified: txn_id=$txn_id, payer_email=$payer_email, token=$token\n", FILE_APPEND);
    }
} else if (strcmp($res, "INVALID") == 0) {
    // 🚨 Invalid IPN
    file_put_contents("ipn_log.txt", "INVALID IPN: $req\n", FILE_APPEND);
}
?>
