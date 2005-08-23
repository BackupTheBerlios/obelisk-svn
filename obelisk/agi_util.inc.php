<?

include_once('common.inc.php');

// some initialisation of the script 
$stdin = fopen("php://stdin", "r");
$stdout = fopen("php://stdout", "w");

set_time_limit(0); // allow menu execution
set_error_handler("agi_error_handler"); // internal error handler

// Fetch parameters
while ($line = agi_read()) {
	$a = explode(":", $line);
	$varname = str_replace("agi_", "", $a[0]);
	$value = trim($a[1]);
	$agivar[$varname] = $value;
	agi_log(DEBUG_INFO, 
		"\$agivar[" . $varname . "] set to '" . $value . "'");
	if ($line == "")
	break;
}
// get the callerId in $callerId

// get the extension in $extension

// get the params as $params[$id]

// some functions
 
/**
 * agi_terminate - close $stdin and $stdout and quit wit $retunn code
 *
 * Global : $stdin, $stdout
 *
 */
function agi_terminate($return_code)
{
	global $stdin, $stdout;

	fclose($stdin);
	fclose($stdout);

	exit($return_code);
}

/**
 * agi_log - write some debug information on the * cli
 *
 * * accept lelve between 1 and 4, the level 0 write the message with the
 *	level 1 and then kill the script
 *
 * PRE: 0 <= $level <= 16 ^ strlen($msg) >= 1
 
 */
function agi_log($level, $msg)
{
	if ($level &  DEBUG_LEVEL)
		agi_write("VERBOSE \"".str_replace("\"", "\\\"", $msg)."\" ".
			$level == 0 ? "1" : "0";
	
	if ($level == 0)
	{
		agi_terminate(-1);
	}
}

/**
 * agi_dial - write asterisk AGI code in order to call $extension as $callerID
 *			using the table dialplan
 *
 * PRE: $extension : a simple extension... ^ is_numeric($extension)
 *	$callerId : extension of the caller inside the local network
 *			^ is_numeric($callerId)
 *	$callerIdFull : complete callerId given by asterisk
 *
 * POST: look the extension & the caller Id in the database in order to write
 *		(agi_write) the AGI script 
 *	si l'extension n'est pas joignable ^ annonce vocale ^ 
 *			return a negative number.
 *		v joingnable ^ ouverture de communication ^ 
 * 			return a positive value (incl. 0)  which is 
 *				the price of the call
 */
function agi_dial($extension, $callerId, $callerIdFull)
{

	agi_log(DEBUG_DEBUG, "obelisk_dial($extension, $isBot)");

	$query = "Select name ".
		 "from Extension, Module ".
		 "where ((end is not null and extension <= $extension ".
		 "  and end >= $callerId) or ".
		 " (end is null and extension = $extension))".
		 " and Module_ID = Module.ID ";

	$query = $db->query($query);
	check_db();

	if ($row = $query->fetchRow(DB_FETCHMODE_ORDERRED)) 
	{
		agi_log(DEBUG_DEBUG, "agi_obelisk.php: extension ".
			$extension." is handeled by : ".$row[0]);
		
		// on tente d'inclure le dial du module en question
		include('modules/'.$row[0].'/dial.inc.php');

		// on génère le nom de la fonction dial
		$fct = $row[0].'_dial';
		return $fct($extension, $callerId, $callerIdFull);
		
		agi_log(DEBUG_INF, "agi_obelisk.php: DONE");
	}
	else
	{
		return agi_notFound($extension, $callerId, $callerIdFull);
	}
}

/**
 * agi_check - check the result of an agi command
 *
 *	(* can output more than one line but the last line contains the
 *		error code )
 *
 * PRE: a line is writed on stdout
 * POST: log error with agi_log(DEBuG_ERROR, ...);
 * RETURN: <0 : ERR
 * 	   >0 == result
 */
function agi_check()
{
	agi_log(DEBUG_DEBUG, "agi_check()");
	
	while($line = agi_read()) {
		$code = substr($line, 0, 4);
		switch($code) {
			case "200 ":
				$a = explode("=", $line);

				// Handle hangup
				if (substr($a[1], 0, 2) == "-1") {
					agi_log(DEBUG_CRIT, 
					     "agi_check(): Hangup detected");
					agi_terminate();
				}

				return $a[1];
			case "510 ":
				agi_log(DEBUG_ERROR, "510 $line");
				return -510;
			case "520 ":
				agi_log(DEBUG_ERROR, "510 $line");
				return -520;
			default:
				agi_log(DEBUG_INF, "--- $line");
		}
	}
	agi_log(DEBUG_CRIT, "agi_check(): there are something strange here");
}

/**
 * agi_write - write AGI command on $stdout
 * 
 * GLOBAL: $stdout - must be initialised to a correct value with fopen();
 * PRE: $agi - valid agi script
 * I/O: stdout .= $agi
 */
function agi_write($agi)
{
	global $stdout;
	
	agi_log(DEBUG_DEBUG, "obelisk_write($agi)");

	$agi = explode("\n", $agi); // explode lines
	
	// for each line in $agi
	foreach ($agi as $line)
	{
		// put some debug information
		agi_log(DEBUG_INFO, "\t$line");
	
		// write the line
		fputs($stdout, $line."\n");
		
		// check the result
		agi_check();
	}
}

/**
 * agi_read - read a line from $stdin in order to get some information from *
 *
 * PRE: /
 * POST: return the line or false on error
 * Global: $stdin
 */
function agi_read()
{
	global $stdin;
		
	agi_log(DEBUG_DEBUG, "agi_read()");
		
	$input = str_replace("\n", "", fgets($stdin, 4096));
	
	agi_log(DEBUG_INF, "agi_read() : \"$input\"");
	
	return $input;
}

/**
 * php error handler... see php4 documentation for specifications
 */
function agi_error_handler($errno, $errstr, $errfile, $errline) 
{
	switch ($errno) {
		case E_USER_ERROR:
			agi_log(DEBUG_ERR, "ERROR  [".$errno."] ".$errstr);
			agi_log(DEBUG_ERR, "  Fatal error in line ".$errline. 
						" of file ".$errfile);
			agi_log(DEBUG_CRIT, "Aborting..."); // kil the script
			
			break;
		case E_USER_WARNING:
			agi_log(DEBUG_WARN, "WARNING [".$errno."] ".$errstr);
			break;
		case E_USER_NOTICE:
			agi_log(DEBUG_INF, "NOTICE [".$errno."] ".$errstr);
			break;
		default:
			agi_log(DEBUG_ERR, "Unkown error type: [".$errno."] ".
						$errstr);
			break;
	}
}

function agi_notFound($extension, $callerId, $callerIdFull)
{
	agi_log(DEBUG_CRIT, "agi_notFound: not yet implemented");
	
	 
	if ($extension = DEFAULT_EXTENSION)
	// impossible de trouver l'extension par défaut
		agi_log(DEBUG_CRIT, 
				"agi_obelisk.php: DEFAULT EXTENSION NOT FOUND");
		else 
		{
			agi_log(DEBUG_INFO, "agi_obelisk.php: NOT FOUND -> ".
				"switching to default extension");
			agi_dial(DEFAULT_EXTENSION, $callerId, $callerIdFull);
		}
	}
}

function agi_callPep($id, $callerId, $callerIdFull)
{
	agi_log(DEBUG_CRIT, "agi_notFound: not yet implemented");
}

?>
