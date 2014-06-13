<?php
function push_message($device_id) {
	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', APN_CERTIFICATE_PATH);
	stream_context_set_option($ctx, 'ssl', 'passphrase', APN_CERTIFICATE_SECRET);

	$fp = stream_socket_client(
			'ssl://gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

	if (!$fp) {
		error_log("errore di connessione al server Apple: $err $errstr");
		return;
	}

	$body['aps'] = array(
			'alert' => 'Hai un nuovo messaggio',
			'sound' => 'default',
			'type' => 'message'
	);

	$payload = json_encode($body);

	$msg = chr(0) . pack('n', 32) . pack('H*', $device_id) . pack('n', strlen($payload)) . $payload;

	$result = fwrite($fp, $msg, strlen($msg));

	if (!$result) {
		error_log('notifica non inviata');
	}

	fclose($fp);
}
