<?php

$error = trim($_REQUEST['error']);
if ($error)
{
	require_once '../admin/include/setup.php';
	$error = json_decode($error, true);

	$error_messages_advertising = [
		'1' => 'VAST XML malformed (could be adblocker)',
		'2' => 'VAST XML doesn\'t define any advertising',
		'3' => 'VAST XML loading failed (could be adblocker)',
		'4' => 'VAST too many redirects',
		'5' => 'device doesn\'t support MP4 files (obsolete)',
		'6' => 'advertising file loading failed (could be adblocker)',
		'7' => 'VAST timeout reached',
		'8' => 'VPAID timeout reached',
		'10' => 'unexpected exception',
	];

	$error_messages_video = [
		'2' => 'client network issue (MediaError code 2)',
		'3' => 'skipping issue (MediaError code 3)',
		'4' => 'start issue (MediaError code 4)',
	];

	$error_message = "code $error[error]";
	if ($error['type'] == 'video' && isset($error_messages_video[$error['error']]))
	{
		$error_message = $error_messages_video[$error['error']];
	} elseif (isset($error_messages_advertising[$error['error']]))
	{
		$error_message = $error_messages_advertising[$error['error']];
	}

	file_put_contents("$config[project_path]/admin/logs/log_player_errors.txt", date("[Y-m-d H:i:s] ") . "[$_SERVER[REMOTE_ADDR]] [$_SERVER[GEOIP_COUNTRY_CODE]] [$error[type]: $error_message] $error[url] [$_SERVER[HTTP_USER_AGENT]] [$_SERVER[HTTP_REFERER]]\n\n", FILE_APPEND | LOCK_EX);
}

header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');