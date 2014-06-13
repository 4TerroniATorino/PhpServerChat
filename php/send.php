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
if (!isset($_REQUEST['recipient']) || !preg_match('/^\+\d{5,19}$/', $_REQUEST['recipient'])) {
	response(1, 'Parametro \'recipient\' assente o non valido');
}
if (!isset($_REQUEST['message']) || empty($_REQUEST['message']) || strlen($_REQUEST['message']) > 1000) {
	response(1, 'Parametro \'message\' assente o non valido');
}

$private_key = $_REQUEST['private_key'];
$phone_number = $_REQUEST['phone_number'];
$recipient = $_REQUEST['recipient'];
$message = $_REQUEST['message'];

// Connessione con il database.
$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB_NAME, MYSQL_PORT);
if ($db->connect_errno) {
	response(3, 'errore interno, impossibile connettersi con il database: ' . $db->connect_errno . ' ' . $db->connect_error);
}

// Controlla il mittente.
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

// Controlla il destinatario e ne estrare le informazioni identificative.
$sql = 'SELECT `device_type`, `device_id` FROM `phone_numbers` WHERE `phone_number` = \'' . $db->real_escape_string($recipient) . '\'';
$res = $db->query($sql);
if ($res == false) {
	$db->close();
	response(3, 'errore interno, impossibile eseguire la query sul database: ' . $db->errno . ' ' . $db->error);
}
if ($res->num_rows == 0) {
	$db->close();
	response(4, 'destinatario non valido');
}
$data = $res->fetch_array(MYSQLI_NUM);
$recipient_device_type = $data[0];
$recipient_device_id = $data[1];

// Salva il messaggio sul database
$sql  = 'INSERT INTO `messages` (`sender`, `recipient`, `message`, `ts_sent`) VALUES (';
$sql .= '\'' . $db->real_escape_string($phone_number) . '\', ';
$sql .= '\'' . $db->real_escape_string($recipient) . '\', ';
$sql .= '\'' . $db->real_escape_string($message) . '\', ';
$sql .= 'CURRENT_TIMESTAMP)';
$res = $db->query($sql);
if ($res == false) {
	$db->close();
	response(3, 'errore interno, impossibile salvare su database: ' . $db->errno . ' ' . $db->error);
}

// Memorizza l'ID assegnato al nuovo messaggio.
$message_id = $db->insert_id;

// Chiude la connessione con il database.
$db->close();

// A seconda della tipologia del device del destinatario, include funzioni push differenti.
if ($recipient_device_type == 'Android') {
	require_once('_push_android.php');
} else if ($recipient_device_type == 'iOS') {
	require_once('_push_ios.php');
}

// Manda la notifica push (se funzione disponibile)
if (function_exists('push_message')) {
	push_message($recipient_device_id);
}

// Bene, consegna la risposta al client.
response(0, 'message sent');
