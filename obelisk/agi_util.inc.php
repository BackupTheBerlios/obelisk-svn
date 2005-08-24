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
	global $db;
	
	agi_log(DEBUG_DEBUG, "obelisk_dial($extension, $isBot)");

	$query = "Select name ".
		 "from Extension, Module ".
		 "where ((end is not null and extension <= $extension ".
		 "  and end >= $extension) or ".
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
 * POST: wait for result and return the last result
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
		$result = agi_check();
	}

	return $result;
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
 * agi_getVar - renvoie le contenu de la variable 
 */
function agi_getVar($varname) 
{
	$result = agi_write("GET VARIABLE \"".$varname."\"");
	$result = preg_replace('/.*\((.*)\).*/i', '${1}', $result);
	agi_log(DEBUG-DEBUG, "agi_getVar '".$varname."' = '".$variable . "'");
	return $result;
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

/**
 * agi_call - place a call on asterisk server. 
 *
 * It executes Dial($callStr...) and add support for prepay system
 *
 * PRE : * if the destination is a local destinatin, this destination exist
 *	 * $extension: only use for logging purpose
 *	 * $callStr, destination for Dial application of asterisk
 *	 * $callOptions << !!!!!!!!!! USE THIS WITH CARE !!!!!!!!
 *	 * $priceConn connection price
 *	 * $price the price/minutes of the call
 * 	 * $callerId a the callerId (only the number)
 *	 * $callerIdFull : full callerId
 *
 *
 * POST : * call is logged
 *	  * prepay card of the callerId is updated if the call succeed 
 * 		^ if the call is not free
 *	  * if the callerId is not a person, it checks for a owner
 *	  	of the callerId in the database and use his prepaid card
 *	  * return the price of the call
 */
	  
function agi_call($extension, $callStr, $callOptions, 
		  $priceConn, $price, 
		  $callerId, $callerIdFull)
{
	global $db;
	
	agi_log(DEBUG_DEBUG, "agi_call : $callSer, $priceConn, ".
				"$price, $callerId, $callerIdFull");
	
	// first I check if the callerId is a valid person in the database
	$query = 	"select P.Credit, P.Announce, P.AskHigherCost ".
		 	"  from People_PrePay_Settings as P,  ".
		 	" where People_Extension = $callerId ";
	$query = $db->query($query);
	check_db();


	if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
	{
		// aucune personne trouvée dans la base de donnée avec 
		// le callerId $callerId --> recherche de la personne 
		// responsable et vérification si elle accepte d'être 
		// facturée pour quelqu'un d'autre...
		$query = "select P.Credit, P.Announce, P.AskHigherCost, ".
			 "	P.Responsable_Extension ".
			 "  from People_PrePay_Settings as P, Extension as E ".
			 " where P.People_Extension = E.Responsable_Extension ".
			 "   and P.AllowOtherCID = true ".
			 "   and ((end is not null and extension<=$callerId".
			 "         and end >= $callerId) or ".
		 	 "        (end is null and extension = $callerId))"
		
		if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
		{
			$found = true;
			$account = $row[3];
			$credit = $row[0];
			$announce = $row[1]; 
			$ask = $row[2];

		}
		else
		{
			// introuvable
			$found = false;
			$accont = ""; 
			$credit = 0; 
			$announce = false; 
			$ask = false;
		}
	}
	else
	{
		// le callerId appartient à une personne de même extension
		$found = true;
		$account = $callerId;
		$credit = $row[0];
		$announce = $row[1]; 
		$ask = $row[2];
	}

	if (($price + $priceConn) > $credit )
		// pas assez de crédit d'appel pour effectuer l'appel en 
		// question
		return agi_dial(NOT_ENOUGH_MONEY, $callerId, $callerIdFull);
	
	// bon on a assez de soux que pour faire l'appel.... on dial
	$maxSec = ($credit-$priceConn)/$price*60;
	agi_write("EXEC DIAL ${callStr}||${callOptions}L(${maxSec})");
	
	$answeredTime = agi_getVar("ANSWEREDTIME");
	$dialStatus = agi_getVar("DIALSTATUS");

	agi_log(DEBUG_INFO, "agi_dial : $extension -> $callStr ".
				": $dialStatus : $answeredTime");
	
	$total = $priceConn + ceil($answeredTime/60);
	if ($dialStatus == "ANSWER")
	{
		agi_credit($account, -($total);
		agi_logCall($extension, $callerId, $account, 
			    $price, $answeredTime, $dialStatus);
	}

	return $total;
}

/** 
 * agi_logCall - log a call into the database
 *
 */
function agi_logCall ($extension, $callerId, $account, $price, $time, $status)
{
	global $db;
	
	agi_log(DEBUG_INF, "agi_logCall : $extension, $callerId, $account, ".
				"$price, $time, $status");
				
	$query = "insert into AgiLog values (DEFAULT, now(), ".
		 "$callerId, $extension, $price, $status)";
	
	$db->query($query);

	check_db();
}

/**
 * agi_credit - add or remove an amount of money from an account
 *
 */
function agi_credit($account, $modification)
{
	global $db;
	
	agi_log(DEBUG_DEBUG, "agi_credit: $account + ($modification) ");
	
	$query = "update People_PrePay_Settings ".
	         "set credit = credit + (${modification}) "
		 "where People_Extension = $callerId ";
	
	$db->query($query);
}

?>
