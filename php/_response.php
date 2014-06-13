<?php
function response($code, $description, $result = NULL) {
	$out = array();
	$out['code'] = $code;
	$out['description'] = $description;
	if (!empty($result)) {
		$out['result'] = $result;
	}
	header('Content-Type: application/json');
	echo(json_encode($out));
	die();
}
