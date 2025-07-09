<?php
$token = $_GET['token'] ?? null;

if ($token) {
    // Look for the token file inside tokens folder (search tokens folder for the token)
    $tokenFiles = glob(__DIR__ . "/tokens/*.txt");

    $found = false;
    $foundFile = '';

    foreach ($tokenFiles as $file) {
        $content = trim(file_get_contents($file));
        if ($content === $token) {
            $found = true;
            $foundFile = $file;
            break;
        }
    }

    if ($found) {
        // Delete the token file to invalidate token
        unlink($foundFile);

        // Serve the download file
        $file = __DIR__ . "/downloads/hyvane_v1.zip";
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

// Token invalid or file missing, redirect home
header("Location: /");
exit;
?>
