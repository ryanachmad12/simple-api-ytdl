<?php
// Skrip ini untuk dijalankan oleh Cron Job

$base_dir = __DIR__ . '/downloader/';
$max_age_seconds = 3600; // 1 jam (60 detik * 60 menit)

// Direktori yang akan dibersihkan
$dirs_to_clean = [$base_dir . 'mp3', $base_dir . 'mp4'];

echo "Memulai proses pembersihan...\n";

foreach ($dirs_to_clean as $dir) {
    if (!is_dir($dir)) continue;

    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $dir . '/' . $file;
        
        // Hapus file jika lebih tua dari $max_age_seconds
        if (time() - filemtime($file_path) > $max_age_seconds) {
            if (unlink($file_path)) {
                echo "Menghapus: $file_path\n";
            } else {
                echo "Gagal menghapus: $file_path\n";
            }
        }
    }
}

echo "Pembersihan selesai.\n";
?>
