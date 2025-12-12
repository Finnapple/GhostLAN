<?php
session_start();

// Check login status
if (
    !isset($_SESSION["ghostlan_admin"]) ||
    $_SESSION["ghostlan_admin"] !== true
) {
    header("Location: admin.php");
    exit();
}

// Check if password was changed by comparing timestamps
$current_timestamp = file_get_contents("session.txt");
if (
    !isset($_SESSION["login_time"]) ||
    $_SESSION["login_time"] < $current_timestamp
) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// delete.php - Clear chatlog and delete all uploads except .htaccess
$chatlog = 'chatlog.txt';
$uploadsDir = __DIR__ . '/uploads/';
$uploadsMeta = __DIR__ . '/uploads_meta.json';

// Delete chatlog
if (file_exists($chatlog)) {
    file_put_contents($chatlog, '');
}

// Delete all files in uploads directory EXCEPT .htaccess
if (is_dir($uploadsDir)) {
    $files = array_diff(scandir($uploadsDir), array('.', '..'));
    foreach ($files as $file) {
        $filePath = $uploadsDir . $file;
        // Skip .htaccess file
        if ($file === '.htaccess') {
            continue;
        }
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}

// Delete uploads metadata (remove .htaccess from metadata if it exists)
if (file_exists($uploadsMeta)) {
    $meta = json_decode(file_get_contents($uploadsMeta), true);
    if (is_array($meta)) {
        // Remove .htaccess from metadata if it exists
        unset($meta['.htaccess']);
        // Save cleaned metadata
        file_put_contents($uploadsMeta, json_encode($meta));
    } else {
        // If metadata is corrupted, delete it
        unlink($uploadsMeta);
    }
}

echo 'OK';
?>
