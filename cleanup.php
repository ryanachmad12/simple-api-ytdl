<?php
// This script is to be run by Cron Job.

$base_dir = __DIR__ . '/downloader/';
$max_age_seconds = 3600; // 1 hour (60 seconds * 60 minutes)

// Directories to be cleaned
$dirs_to_clean = [$base_dir . 'mp3', $base_dir . 'mp4'];

echo "[*] Starting the cleaning process...\n";

foreach ($dirs_to_clean as $dir) {
    if (!is_dir($dir)) continue;

    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $dir . '/' . $file;
        
        // Hapus file jika lebih tua dari $max_age_seconds
        if (time() - filemtime($file_path) > $max_age_seconds) {
            if (unlink($file_path)) {
                echo "Deleting: $file_path\n";
            } else {
                echo "Failed to delete: $file_path\n";
            }
        }
    }
}

echo "Cleaning Completed.\n";
?>
