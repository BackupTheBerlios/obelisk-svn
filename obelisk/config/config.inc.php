<?php

// the path where pear is installed
ini_set('include_path', '.'.PATH_SEPARATOR.'/usr/share/php';

// the database dsn 
$dsn = "protocol://user:pass@host/dbname";

// default extension for a non-existing number. 
// this extension have to be define in the dialplan table for each 
// callerID.
//
// if obelisk cannot found a corresponding default extension, it hangup...
$default_extension = 0;

// debug level
define('DEBUG_LEVEL', 1); 	// INFO
//define('DEBUG_LEVEL', 2); 	// WARNING
//define('DEBUG_LEVEL', 4); 	// ERROR
//define('DEBUG_LEVEL', 8);	// CRITICAL

?>
