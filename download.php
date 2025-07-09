<?php
$token = $_GET['token'] ?? null;

if ($token && file_exists("valid_tokens.txt")) {
    $tokens = file("valid_tokens.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (in_array($token, $tokens)) {
        // Remove the token so it can't be reused
        $tokens = array_filter($tokens, fn($t) => $t !== $token);
        file_put_contents("valid_tokens.txt", implode(PHP_EOL, $tokens));

        // Download the file
        $file = "downloads/hyvane_v1.zip";
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }
}
// If token invalid or file missing, redirect to homepage
header("Location: /");
exit;
?>
