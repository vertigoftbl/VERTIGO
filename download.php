<?php
// Get the transaction ID from the URL (PayPal returns it as 'tx')
if (isset($_GET['tx'])) {
    $txn_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['tx']); // sanitize

    $tokenFile = __DIR__ . "/tokens/$txn_id.txt";
    if (file_exists($tokenFile)) {
        $token = trim(file_get_contents($tokenFile));

        // Redirect to download.php with the token
        header("Location: download.php?token=" . urlencode($token));
        exit;
    } else {
        // Token not found (maybe IPN hasn't come in yet)
        echo "Your payment is being processed. Please wait a moment and refresh this page.";
        // Optionally add meta-refresh or JS to retry after few seconds
        exit;
    }
} else {
    // No tx parameter, just redirect to homepage or show message
    header("Location: /");
    exit;
}
?>
