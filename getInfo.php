<?php
// File: getInfo.php
header('Content-Type: application/json');

define('YT_DLP_PATH', '/usr/local/bin/yt-dlp');

function send_json_response($data, $success = true) {
    echo json_encode(['success' => $success, 'data' => $data]);
    exit;
}

if (empty($_GET['url'])) {
    send_json_response(['message' => 'The URL parameter does not exist.'], false);
}

$youtube_url = $_GET['url'];
if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/', $youtube_url)) {
    send_json_response(['message' => 'The YouTube URL is invalid.'], false);
}

$safe_url = escapeshellarg($youtube_url);
$command = YT_DLP_PATH . ' -4 --dump-single-json ' . $safe_url;
$json_output = shell_exec($command);

if (empty($json_output)) {
    send_json_response(['message' => 'Failed to retrieve video information. The URL may be invalid or the video may be private.'], false);
}

$video_data = json_decode($json_output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_response(['message' => 'Failed to parse video data.'], false);
}

$response_data = [
    'title' => htmlspecialchars($video_data['title'] ?? 'Title Not Found', ENT_QUOTES, 'UTF-8'),
    'thumbnail' => filter_var($video_data['thumbnail'] ?? '', FILTER_VALIDATE_URL)
];

send_json_response($response_data);
?>
