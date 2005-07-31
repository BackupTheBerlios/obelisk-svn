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

// debug level for AGI script
define('DEBUG_LEVEL', 255); 	// EVERITHINGS
//define('DEBUG_LEVEL', 1); 	// DEBUG
//define('DEBUG_LEVEL', 2); 	// INFO
//define('DEBUG_LEVEL', 4); 	// WARNING
//define('DEBUG_LEVEL', 8);	// ERROR
//define('DEBUG_LEVEL', 16);	// CRITICAL

?>
