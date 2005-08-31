<?php

// the path where pear is installed
DEFINE('PEAR_PATH', '/usr/share/php');

//PATH for AGI script 
DEFINE('AGI_PATH', '/usr/share/obelisk');

// the database dsn 
$dsn = "pgsql://obelisk:obeliskpwd@localhost/obelisk";

// default extension for a non-existing number. 
// this extension have to be define in the dialplan table for each 
// callerID.
//
// if obelisk cannot found a corresponding default extension, it hangup...
DEFINE('DEFAULT_EXTENSION', 0);
DEFINE('NOT_ENOUGH_MONEY', '1'); // NOT YET IMPLEMENTED
DEFINE('END_OF_MONEY', 2);	 // NOT YET IMPLEMENTED


// debug level (see common.inc.php for more informations)
DEFINE('DEBUG_LEVEL', 4);

// if you use ser before your asterisk server set USE_SER to true and SER_IP
// to the ip of the ser serveur.
// obelisk doesn't support ser at the present time
DEFINE('USE_SER', false);
DEFINE('SER_IP', '192.168.3.57');
DEFINE('ASTERISK_SIP_PORT', 5061); 
DEFINE('ASTERISK_IP', '192.168.3.57');
?>
