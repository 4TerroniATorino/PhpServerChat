<?php
require_once('_config.php');
require_once('_response.php');

// Recupera e convalida i parametri di input.
if (!isset($_REQUEST['private_key']) || empty($_REQUEST['private_key'])) {
	response(1, 'Parametro \'private_key\' assente o non valido');
}
if (!isset($_REQUEST['phone_number']) || !preg_match('/^\+\d{5,19}$/', $_REQUEST['phone_number'])) {
	response(1, 'Parametro \'phone_number\' assente o non valido');
}

$private_key = $_REQUEST['private_key'];
$phone_number = $_REQUEST['phone_number'];

// Connessione con il database.
$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB_NAME, MYSQL_PORT);
if ($db->connect_errno) {
	response(3, 'errore interno, impossibile connettersi con il database: ' . $db->connect_errno . ' ' . $db->connect_error);
}

// Controlla il richiedente.
$sql = 'SELECT 1 FROM `phone_numbers` WHERE `phone_number` = \'' . $db->real_escape_string($phone_number) . '\' AND  `private_key` = \'' . $db->real_escape_string($private_key) . '\'';
$res = $db->query($sql);
if ($res == false) {
	$db->close();
	response(3, 'errore interno, impossibile eseguire la query sul database: ' . $db->errno . ' ' . $db->error);
}
if ($res->num_rows == 0) {
	$db->close();
	response(2, 'phone number non valido');
}

// Cerca i messaggi non letti sul database.
$sql = 'SELECT `id`, `sender`, `recipient`, `message`, `ts_sent` FROM `messages` WHERE `recipient` = \'' . $db->real_escape_string($phone_number) . '\'';
$res = $db->query($sql);
if ($res == false) {
	$db->close();
	response(3, 'errore interno, impossibile eseguire la query sul database: ' . $db->errno . ' ' . $db->error);
}
// Trasferisce i risultati in un array.
$messages = array();
while (($data = $res->fetch_array(MYSQLI_NUM)) != NULL) {
	$aux = array();
	$aux['id'] = $data[0];
	$aux['sender'] = $data[1];
	$aux['recipient'] = $data[2];
	$aux['message'] = $data[3];
	$aux['ts_sent'] = date('Y/m/d H:i:s O', time($data[4]));
	$messages[] = $aux;
}

// Cancella i messaggi dal database.
foreach ($messages as $message) {
	$sql = 'DELETE FROM `messages` WHERE `id` = ' . $message['id'];
	$db->query($sql);
}

// Chiude la connessione con il database.
$db->close();

// Consegna la risposta al client.
$size = count($messages);
if ($size > 0) {
	$result = array();
	$result['messages'] = $messages;
} else {
	$result = NULL;
}
response(0, $size . ' message(s) retrieved', $result);
