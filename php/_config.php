<?php
error_reporting(E_ERROR | E_PARSE);

// Coordinate per l'accesso al database.
define('MYSQL_HOST', '<indirizzo del database server>');
define('MYSQL_PORT', 3306);
define('MYSQL_USER', '<username del database server>');
define('MYSQL_PASS', '<password del database server>');
define('MYSQL_DB_NAME', '<nome del database>');

// API Key per l'invio di push notification verso Android (servizio GCM)
define('GCM_API_KEY', '<API key per inviare le push notification di google>');

// Certificato per l'invio delle push notification version iOS
define('APN_CERTIFICATE_PATH', 'secret/ck.pem');
define('APN_CERTIFICATE_SECRET', '<password del certificato>');
