<?php

include('config/config.inc.php');

require_once('DB.php'); // PEAR DB

// DEBUG_LEVEL
define('DEBUG_DEBUG', 4);
define('DEBUG_INFO', 3);
define('DEBUG_WARN', 2);
define('DEBUG_ERR', 1);
define('DEBUG_CRIT', 0);	// "die" the script

// connection à la base de donnÃ©e
$db = DB::connect($dsn);

if (DB::isError($db))
	die ($db->getMessage());


function check_db($db)
{
	if (DB::isError($db))
		die ($db->getMessage()."\n");
}

?>
