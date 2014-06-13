<?php
function push_message($device_id) {
	$data = array();
	$data['type'] = 'message';
	$url = 'https://android.googleapis.com/gcm/send';
	$registrationIDs = array();
	$registrationIDs[] = $device_id;
	$fields = array();
	$fields['registration_ids'] = $registrationIDs;
	$fields['collapse_key'] = 'message';
	$fields['data'] = $data;
	$headers = array();
	$headers[] = 'Authorization: key=' . GCM_API_KEY;
	$headers[] = 'Content-Type: application/json';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	curl_close($ch);
}
