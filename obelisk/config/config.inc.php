<?php

// the path where pear is installed
DEFINE('PEAR_PATH', '/usr/share/php');

//PATH for AGI script 
DEFINE('AGI_PATH', '/usr/share/obelisk');

// the database dsn 
$dsn = "pgsql://obelisk:obeliskpwd@localhost/obelisk";


// define here sound sequence ID for the folowing events
DEFINE('SOUNDSET_NOT_FOUND', 1);
DEFINE('SOUNDSET_END_OF_MONEY', 2);
DEFINE('SOUNDSET_NOT_ENOUGH_MONEY', 3);


// debug level (see common.inc.php for more informations)
DEFINE('DEBUG_LEVEL', 3); // 4 produce a lot of debug and seems to crash 
			  // asterisk some times

?>
