<?php
// File: prepareDownload.php
set_time_limit(300);
header('Content-Type: application/json');

define('YT_DLP_PATH', '/usr/local/bin/yt-dlp');
function send_json_response($data, $success = true) { echo json_encode(['success' => $success, 'data' => $data]); exit; }
function sanitize_filename($filename) { $filename = preg_replace('/[<>:"\/\\|?*]/', '', $filename); $filename = preg_replace('/\s+/', ' ', $filename); $filename = trim($filename); if (strlen($filename) > 150) { $filename = substr($filename, 0, 150); } if (empty($filename)) { $filename = 'downloaded_file'; } return $filename; }

if (empty($_GET['url']) || empty($_GET['format']) || empty($_GET['title'])) { send_json_response(['message' => 'Parameter tidak lengkap.'], false); }
$youtube_url = $_GET['url'];
$target_format = strtolower($_GET['format']);
$title = $_GET['title'];

if (!in_array($target_format, ['mp3', 'mp4'])) { send_json_response(['message' => 'Format tidak valid.'], false); }
if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/', $youtube_url)) { send_json_response(['message' => 'URL YouTube tidak valid.'], false); }

$safe_url = escapeshellarg($youtube_url);
$safe_filename = sanitize_filename($title);
$filename_with_ext = $safe_filename . '.' . $target_format;
$target_dir = __DIR__ . '/downloader/' . $target_format . '/';
if (!is_dir($target_dir)) { mkdir($target_dir, 0775, true); }
$full_output_path = $target_dir . $filename_with_ext;

$command = '';
if ($target_format === 'mp4') {
    $command = sprintf('%s -4 -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" --merge-output-format mp4 -o %s %s', YT_DLP_PATH, escapeshellarg($full_output_path), $safe_url);
} else {
    $command = sprintf('%s -4 -x --audio-format mp3 -o %s %s', YT_DLP_PATH, escapeshellarg($full_output_path), $safe_url);
}

$output = shell_exec($command . ' 2>&1');

if (!file_exists($full_output_path)) {
    error_log("Gagal membuat file. Perintah: $command. Output: $output");
    send_json_response(['message' => 'Gagal mengonversi video di server.'], false);
}

$download_link = 'downloader/' . $target_format . '/' . $filename_with_ext;
send_json_response(['download_url' => $download_link]);
?>
