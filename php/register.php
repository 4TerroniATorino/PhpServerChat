<?php
require_once('_config.php');
require_once('_response.php');

// Recupera e convalida i parametri di input.
if (!isset($_REQUEST['phone_number']) || !preg_match('/^\+\d{5,19}$/', $_REQUEST['phone_number'])) {
	response(1, 'Parametro \'phone_number\' assente o non valido');
}
if (!isset($_REQUEST['device_type']) || ($_REQUEST['device_type'] != 'Android' && $_REQUEST['device_type'] != 'iOS')) {
	response(1, 'Parametro \'device_type\' assente o non valido');
}
if (!isset($_REQUEST['device_id']) || !preg_match('/^\S{5,255}$/', $_REQUEST['device_id'])) {
	response(1, 'Parametro \'device_id\' assente o non valido');
}

$phone_number = $_REQUEST['phone_number'];
$device_type = $_REQUEST['device_type'];
$device_id = $_REQUEST['device_id'];

// Connessione con il database.
$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB_NAME, MYSQL_PORT);
if ($db->connect_errno) {
	response(3, 'errore interno, impossibile connettersi con il database: ' . $db->connect_errno . ' ' . $db->connect_error);
}

// Genera una private key per la entry che si sta per salvare sul database.
$private_key = md5(uniqid('freem', TRUE));

// Il record esiste già?
$sql = 'SELECT 1 FROM `phone_numbers` WHERE `phone_number` = \'' . $db->real_escape_string($phone_number) . '\'';
$res = $db->query($sql);
if ($res == false) {
	$db->close();
	response(3, 'errore interno, impossibile eseguire la query sul database: ' . $db->errno . ' ' . $db->error);
}
if ($res->num_rows == 0) {
	// Record non presente sul database: da creare.
	$sql  = 'INSERT INTO `phone_numbers` (`phone_number`, `device_type`, `device_id`, `private_key`) VALUES (';
	$sql .= '\'' . $db->real_escape_string($phone_number) . '\', ';
	$sql .= '\'' . $db->real_escape_string($device_type) . '\', ';
	$sql .= '\'' . $db->real_escape_string($device_id) . '\', ';
	$sql .= '\'' . $db->real_escape_string($private_key) . '\')';
} else {
	// Record presente sul database: da aggiornare.
	$sql  = 'UPDATE `phone_numbers` SET ';
	$sql .= '`device_type` = \'' . $db->real_escape_string($device_type) . '\', ';
	$sql .= '`device_id` = \'' . $db->real_escape_string($device_id) . '\', ';
	$sql .= '`private_key` = \'' . $db->real_escape_string($private_key) . '\' ';
	$sql .= 'WHERE `phone_number` = \'' . $db->real_escape_string($phone_number) . '\'';
}
// Esegue l'update su database.
$res = $db->query($sql);
if ($res == false) {
	$db->close();
	response(3, 'errore interno, impossibile aggiornare il database: ' . $db->errno . ' ' . $db->error);
}

// Chiude la connessione con il database.
$db->close();

// Bene, consegna la risposta al client.
$result = array();
$result['private_key'] = $private_key;
response(0, 'phone number registered', $result);
