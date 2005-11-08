<?php

// some initialisation of the script 
$stdin = fopen("php://stdin", "r");
$stdout = fopen("php://stdout", "w");

// now we record headers lines in $buffer before sending anything on stdout
$buffer = array();
while ($bufferTemp = trim(fgets($stdin, 4096)))
{
	if ($bufferTemp == "")
		break;
	array_push($buffer, $bufferTemp);
}

// now let's go...
include_once('common.inc.php');

// a call object
// this object will contain all information about a call
class callObj {
	var $callerId, $extension;
	var $responsable, $isLocalPep;
	var $agiVars;
	var $lastTryStatus; // return status of Dial application

	// constructor
	// if responsable is not found : responsable = '' = a valid
	//	but impossible extension. --> no credit
	function callObj($agi)
	{
		global $db;
		
		$this->agiVars = $agi;
		$this->callerId = trim(preg_replace('/.*<(\d+)>.*/i', '${1}', 
					$this->agiVars["callerid"]));
		$this->extension = $this->agiVars["extension"];
	
		$query = "select count(*) from People ".
				"where Extension = '".$this->callerId."}'";
		
		$query = $db->query($query);
		check_db($query);

		if ($row=$query->fetchRow(DB_FETCHMODE_ORDERED))
			$this->isLocalPep = true;
		else
		{
			$this->isLocalPep = false;

			// recherche du responsable
			
			$query = "Select Responsable_Extension ".
			   "from Extension ".
    			   "where ((ext_end is not null and ".
			   "extension_type_comp(extension,  '".
			   			$this->callerId.") <= 0 ".
			   "and extension_type_comp(ext_end,  '".
			   			$this->extension."') >= 0) or ".
			   " (ext_end is null and extension = '".
			   			$this->extension."')) and ".
			   " Responsable_Extension is not null";

			 $query = $db->query($query);
			 check_db($query);

			 
			if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
				// responsable non trouv� : 
				$this->responsable = '';
			else
				$this->responsable = $row[0];
		}

		$this->lastTryStatus = NULL;

	}

	function is_local()
	{
		return $this->isLocalPep;
	}

	function get_responsable() // return $cid when is_local();
	{
		if ($this->isLocalPep)
			return $this->callerId;
		else
			return $this->responable;
	}
	
	// return numeric callerId
	function get_cid()
	{
		return $this->callerId;
	}

	// return full callerId
	function get_cidFull()
	{
		return $this->agiVars["callerid"];
	}

	function get_extension()
	{
		return $this->extension;
	}

	function set_extension($new_ext)
	{
		$this->extension = $new_ext;
	}

	function set_lastTryStatus($status)
	{
		$this->lastTryStatus = $status;
	}

	function get_lastTryStatus()
	{
		return $this->lastTryStatus;
	}
}
		
set_time_limit(0); // allow menu execution
set_error_handler("agi_error_handler"); // internal error handler

// now let's go...
// Fetch parameters
foreach ($buffer as $line) {
	$a = explode(":", $line);
	$varname = str_replace("agi_", "", $a[0]);
	$value = trim($a[1]);
	$agivar[$varname] = $value;
	if ($line == "")
		break;
}

// now let's go...
// just for debuging purpose
foreach($agivar as $varname => $value)
	agi_log(DEBUG_DEBUG, "\$agivar[" . $varname . "] set to '" . $value . "'");

// now we create the $call obejct
$call = new callObj($agivar);

// now let's go...
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
	static $agi_log_lock; // this variable is set when this function report
			      // somthing. It's used only by this function in 
			      // oerder to prevent agi_write to report 
			      // something using agi_log and create a stack
			      // overflow

	if ($agi_log_lock)	return;
	
	$agi_log_lock = true;
	if ($level <=  DEBUG_LEVEL)
		agi_write("VERBOSE \"".str_replace("\"", "\\\"", $msg)."\" ".
			($level == 0 ? "1" : $level));
	
	if ($level == 0)
	{
		agi_terminate(-1);
	}

	$agi_log_lock = false;
}

/**
 * agi_dial - write asterisk AGI code in order to call $extension as $callerID
 *			using the table dialplan
 *
 * PRE: $call : a complete ObjCall
 *
 * POST: look the extension & the caller Id in the database in order to write
 *		(agi_write) the AGI script 
 *	si l'extension n'est pas joignable ^ annonce vocale ^ 
 *			return a negative number.
 *		v joingnable ^ ouverture de communication ^ 
 * 			return a positive value (incl. 0)  which is 
 *				the price of the call
 * 		joignable = existe, trait�e par un module et que le caller a le
 *			droit de joindre
 */
function agi_dial(&$call)
{
	global $db;
	
	$extension = $call->get_extension();
	$callerId = $call->get_cid();
	$callerIdFull = $call->get_cidFull();
	
	agi_log(DEBUG_DEBUG, "agi_dial($extension, $callerId, ".
					"$callerIdFull)");

	// on recherche a quel module appartient l'extension et si le 
	// responsable peut joindre cette extension
	$query = "Select name ".
		 "from Extension, Module, Rights, Grp_has_People ".
		 "where ((ext_end is not null and ".
		 "extension_type_comp(extension,  '$extension') <= 0 ".
		 "and extension_type_comp(ext_end,  '$extension') >= 0) or ".
		 " (ext_end is null and extension = '$extension'))".
		 " and Extension.Module_ID = Module.ID ".
		 "and Grp_has_People.People_Extension = ".
		 			$call->get_responsable()." ".
		 "and Grp_has_People.Grp_ID = Rights.Grp_ID ".
		 "and Rights.Module_Action_ID = 0".
		 "and Rights.Module_ID = Module.ID";


	$query = $db->query($query);
	check_db($query);
	
	if ($row = $query->fetchRow(DB_FETCHMODE_ORDERED)) 
	{
		agi_log(DEBUG_INFO, "agi_dial(): extension ".
			$extension." is handeled by : ".$row[0]);
		
		// on tente d'inclure le dial du module en question
		include('modules/'.$row[0].'/dial.inc.php');

		// on génère le nom de la fonction dial
		$fct = $row[0].'_dial';
		return $fct($call);
		
		agi_log(DEBUG_INF, "agi_obelisk.php: DONE");
	}
	else
	{
		return agi_notFound($call);
	}
}

/**
 * agi_check - check the result of an agi command
 *
 *	(* can output more than one line but the last line contains the
 *		error code )
 *
 * PRE: a line is writed on stdout
 *	* $no_kill : if true: agi_check will not kill the script if asterisk 
 *		return an error. This could be use for example in order to 
 *		bill the start of the communication of dial an other number
 * POST: log error with agi_log(DEBuG_ERROR, ...);
 * RETURN: <0 : ERR
 * 	   >0 == result
 */
function agi_check($no_kill = false)
{
//	agi_log(DEBUG_DEBUG, "agi_check()");
	
	while($line = agi_read()) {
		if ($line == "") {
			// white line in asterisk output 
			// --> error
			if ($no_kill)
				return -1;
			else
				agi_log(DEBUG_CRIT, 
					"agi_check(): * output error");
		}
		$code = substr($line, 0, 4);
		switch($code) {
			case "200 ":
				$a = explode("=", $line, 2);

				// Handle hangup
				if (substr($a[1], 0, 2) == "-1") 
					if ($no_kill)
						return -1;
					else
						agi_log(DEBUG_CRIT, 
					  "agi_check(): Hangup detected");

				return $a[1];
			case "510 ":
				agi_log(DEBUG_ERR, "510 $line");
				return -510;
			case "520 ":
				agi_log(DEBUG_ERR, "510 $line");
				return -520;
			default:
				agi_log(DEBUG_INFO, "--- $line");
		}
	}
	
	if ($no_kill)
		return -1;
	else
		agi_log(DEBUG_CRIT, "agi_check(): there is something ".
				"strange here");
}

/**
 * agi_write - write AGI command on $stdout
 * 
 * GLOBAL: $stdout - must be initialised to a correct value with fopen();
 * PRE: $agi - valid agi script as a long string. (\n as line delimiter)
 *	* $no_kill : if true: agi_check will not kill the script if asterisk 
 *		return an error. This could be use for example in order to 
 *		bill the start of the communication of dial an other number
 * I/O: stdout .= $agi
 * POST: wait for result and return the last result (use agi_check)
 */
function agi_write($agi, $no_kill = false)
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
		$result = agi_check($no_kill);
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
		
	$input = str_replace("\n", "", fgets($stdin, 4096));
	
	agi_log(DEBUG_DEBUG, "agi_read() : \"$input\"");
	
	return $input;
}

/**
 * agi_getVar - renvoie le contenu de la variable 
 */
function agi_getVar($varname) 
{
	$result = agi_write("GET VARIABLE \"".$varname."\"");
	$result = preg_replace('/.*\((.*)\).*/i', '${1}', $result);
	agi_log(DEBUG_DEBUG, "agi_getVar '".$varname."' = '".$result. "'");
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
					$errstr." in ".$errfile.":".$errline);
			break;
	}
}

/**
 * agi_notFound - send an info tone to the dialer and log the call
 *			this return -1 because it's always a "bad call"
 *			the price of ths call il always null 
 */
function agi_notFound(&$call)
{
	agi_log(DEBUG_ERR, "agi_notFound !!!");

	agi_logCall (&$call, 0, 0, 'NOT FOUND');

	while (agi_play_soundSet(SOUNDSET_NOT_FOUND, "") == 0)
	{
		sleep(3);
	}

	return -1;
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
 *	 * $priceConn connection price >= 0
 *	 * $price the price/minutes of the call >= 0
 * 	 * $callerId a the callerId (only the number)
 *	 * $callerIdFull : full callerId
 *	 * logIfFailed : if the function have to log a call even if it failed.
 *
 *
 * POST : * call is logged
 *	  * prepay card of the callerId is updated if the call succeed 
 * 		^ if the call is not free
 *	  * if the callerId is not a person, it checks for a owner
 *	  	of the callerId in the database and use his prepaid card
 *	  * return the price of the call
 *	  * $call->set_lastTryStatus with the return status of Application Dial
 *		in case of 'not enough of money' : "MONEY'
 */
	  
function agi_call(&$call, $callStr, $callOptions, 
		  $priceConn, $price, $logIfFailed = true)
{
	global $db;
	
	$extension = $call->get_extension();
	$callerId = $call->get_cid();
	$callerIdFull = $call->get_cidFull();
	$account = $call->get_responsable();

	agi_log(DEBUG_DEBUG, "agi_call : $callStr, $priceConn, ".
				"$price, $callerId, $callerIdFull");
	
	// first I check if the callerId is a valid person in the database
	$query = 	"select P.Credit, P.Announce, P.AskHigherCost, ".
			"	P.AllowOtherCID ".
		 	"  from People_PrePay_Settings as P  ".
		 	" where People_Extension = $account";
	$query = $db->query($query);
	check_db($query);


	if ((!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
		or (($account != $callerId) and !$row[3]))
	{
		// introuvable ou le responsable qui ne prend pas en charge
		$found = false;
		$credit = 0; 
		$announce = false; 
		$ask = false;
	}
	else
	{
		$found = true;
		$credit = $row[0];
		$announce = $row[1]; 
		$ask = $row[2];
	}
	
	if (($price + $priceConn) > $credit )
		// pas assez de crédit d'appel pour effectuer l'appel en 
		// question
	{
		agi_log(DEBUG_INFO, 
			"agi_call(): not enough money");
		agi_play_soundSet(SOUNDSET_NOT_ENOUGH_MONEY, "");
		$call->set_lastTryStatus("MONEY");
	
		if ($logIfFailed)
			agi_logCall($call, $price, 0, "MONEY");
		return -1;
	}

	
	// bon on a assez de soux que pour faire l'appel.... on dial
	if ($price == 0)
		agi_write("EXEC DIAL ${callStr}||${callOptions}",true);
	else
	{
		$maxSec = ($credit-$priceConn)/$price*60*1000;
		agi_write("EXEC DIAL ${callStr}||${callOptions}L(${maxSec})",true);
	}
	$answeredTime = agi_getVar("ANSWEREDTIME");
	$dialStatus = agi_getVar("DIALSTATUS");

	$call->set_lastTryStatus($dialStatus);
	
	agi_log(DEBUG_INFO, "agi_dial : $extension -> $callStr ".
				": $dialStatus : $answeredTime");
	
	$total = $priceConn + ceil($answeredTime/60)*$price;
	if ($dialStatus == "ANSWER")
	{
		agi_logCall($call, $price, $answeredTime, $dialStatus);
		agi_credit($account, -($total));
	}
	else if ($logIfFailed)
		agi_logCall($call, $price, $answeredTime, $dialStatus);

	return $total;
}

/** 
 * agi_logCall - log a call into the database
 *
 */
function agi_logCall (&$call, $price, $time, $status)
{
	global $db;
	
	$extension = $call->get_extension();
	$callerId = $call->get_cid();
	$account = $call->get_responsable();
	
	agi_log(DEBUG_INFO, "agi_logCall : $extension, $callerId, $account, ".
				"$price, $time, $status");
				
	$query = "insert into AgiLog values (DEFAULT, $account, now(), ".
		 "$callerId, $extension, $price, $time, '$status')";

	$db->query($query);
	check_db($query);
}

/**
 * agi_credit - add or remove an amount of money from an account
 *
 */
function agi_credit($account, $modification)
{
	global $db;

	agi_log (DEBUG_INFO, "agi_credit($account, $modification)");

	$query = "update People_PrePay_Settings ".
	         "set credit = credit + (${modification}) ".
		 "where People_Extension = $account ";
	
	$db->query($query);

	db_check($db);
}

/**
 * agi_play - play a sound and allow $dtmf to stop this sound. 
 *		
 *		This function doesn't check if the file really exist but if the
 *		Id of the sound doesn't exist it's return -1
 *
 * PRE: $soundFile : sound filename
 *	$dtmf : allowed dtmf
 * POST: $soundId is played if the Id is corrected
 *	return 	-1 : if there is an error
 *		 0 : if the song is correctly played and not stopped
 *		 a dtmf (0 is return as 10, # as '#' and * as '*'
 */
function agi_play($soundFile, $dtmf = "0123456789#*")
{
	global $db;
	
	$result = agi_write("STREAM FILE \"".$soundFile."\" \"${dtmf}\"", true);
	/* 
		Returns:
		failure: -1 endpos=<sample offset>
		failure on open: 0 endpos=0
		success: 0 endpos=<offset>
		digit pressed: <digit> endpos=<offset>
	*/
  	$first = explode(" ", $result);
	$first = $first[0];

	$second = explode("=", $result);
	$second = $second[1];

	if ($first == -1 || ($first == 0 && $second == 0))
		return -1; // failure
	
	if ($first == 0)
		return 0;  // success
	
	$first = chr($first);

	if ($first == 0)
		$first = 10;
	
	return $first;
}

/**
 * agi_sayDigits - like agi_lay but says digits instead of a soundset :
 *
 *	1234 is played  : '1', '2', '3', '4'
 */
function agi_sayDigits($digits, $dtmf = "#")
{
	$result = agi_write("SAY DIGITS $digits \"${dtmf}\"", true);
	/* 
		Returns:
		failure: -1
		success: 0 
		digit pressed: <digit>
	*/
  
	if ($result == -1)
		return -1; // failure
	
	if ($result == 0)
		return 0;  // success
	
	$result = chr($result);

	if ($result == 0)
		$result = 10;
	
	return $result;
}

/**
 * agi_sayNumber - like agi_lay but says digits instead of a soundset :
 *
 *	1234 is played  : "one thousand two hundred and thirty four"
 */
function agi_sayNumber($digits, $dtmf = "#")
{
	$result = agi_write("SAY NUMBER $digits \"${dtmf}\"", true);
	/* 
		Returns:
		failure: -1
		success: 0 
		digit pressed: <digit>
	*/
  
	if ($result == -1)
		return -1; // failure
	
	if ($result == 0)
		return 0;  // success
	
	$result = chr($result);

	if ($result == 0)
		$result = 10;
	
	return $result;
}

/**
 * agi_play_soundSet - play a soundset.
 *
 * PRE : *soundSetId
 * POST : same as above
 *
 *      it reads the sound respecting the priority flag of the databse
 *	and stop all the sound after the first matching dtmf
 *
 */
function agi_play_soundSet($soundSetId, $dtmf = "0123456789*#")
{
	global $db;

	$query = "select Filename ".
		 "from AgiSound, AgiSound_Set ".
		 "where AgiSound.ID = AgiSound_ID ".
		 " and AgiSound_Set.ID = $soundSetId ".
		 "order by Priority";

	$query = $db->query($query);
	check_db($query);
	
	unset($result);
	while ($row = $query->fetchRow(DB_FETCHMODE_ORDERED))
	{
		$result = agi_play($row[0], $dtmf);

		if ($result != 0)
			return $result;
	}
	if (!isset($result))
		return -1;
	else
		return 0;
}

/**
 * agi_playSoundSet_read - play a soundset and read at least $min digit and
 *				maximum $max digit from the user. The user has
 *				to use the pound key in order to enter a 
 *				number of digit lower than the $maximum number
 *				of digit. 
 *
 * PRE: * $soundSetId : the Id of the soundset. If not found, return -1
 *	* $min the minimum number of digit. Below this number pound keu is 
 *		ignored
 *	* $max must be higher or equal to the minimum.
 * 	* $timeout : if the user doesn't enter any digit after $timeout seconds
 *			it replays the soundset.
 *	* $max_replay : maximum number of timeout --> return -1;
 *
 * POST:* plays the soundset and stops it after received the first digit
 *	* the star key replay the soundSet
 *	* return positive number = the value that the user enter
 *			 < 10^($max)-1
 *	        negative number : an error
 *	 	zero is a mositive number
 *	
 *	*    if the user enter 007, and that the minimum is 3, it's accepted
 *		but return an integer : 7
 */
function agi_play_soundSet_read($soundSetId, $min, $max, $timeout = 20, 
					$max_replay=3)
{
	$ndigits = 0;
	$result = 0;
	$tempRes = agi_play_soundSet($soundSetId);
	$max_replay--;

	if ($tempRes == 0)
		// pas de digit lue, on fait une première pause
		// dans le but de ne pas faire entendre à l'utilisateur le
		// prompt immédiatement après
		$tempRes = agi_readDigit($timout);

	while ($ndigits < $max)
	{
		if ($tempRes == -1)
			return -1;	// error
		else if ($tempRes == 0)
		{
			// pas de touche appuyée au bout du timeout
			// --> relecture

			$tempRes = agi_readDigit($timeout);

			if ($max_replay <= 0) return -1;
			$tempRes = agi_play_soundSet($soundSetId);
			$max_replay--;

			if ($tempRes == 0)
				$tempRes = agi_readDigit($timout);

			continue; // on reprend le parsing
		}
		else if ($tempRes == 10)
			$tempRes = 0;
		else if ($tempRes == "*") // replay
		{
			$tempRes = 0;	// force le timeout
			continue;
		}
		else if ($tempRes == "#")
		{
			if ($ndigits >= $min)
				return $result;
			else
			// force le timeout
			{
				$tempRes = 0;
				continue;
			}
		}

		// si nous sommes ici c'est que l'on a lu un chiffre correcte
		// et qu'il se trouve dans tempRes : 
		$ndigits++;
		$result = $result*10 + $tempRes;

		// on lance la lecture du nombre suivant : 
		$tempRes = agi_readDigit($timout);
	}
	// nombre maximum de digit atteind :
	return $result;
}

/**
 * agi_playTone - play a tone for $nSec seconds
 *
 */
function agi_playTone($tone, $nSec) 
{
	agi_log(DEBUG_DEBUG, "Playing tone ".$tone." for ".$nSec." seconds");
	agi_write("EXEC Playtones ".$tone);
	sleep($nSec);
	agi_write("EXEC StopPlaytones");
	return 0; // price : 0
}


/**
 * agi_goto - goto an other asterisk context.
 *
 *
 * PRE: $context is a valid asterisk $context
 * POST: goto $context
 *       this disable every cdr log
 */
function agi_goto($context)
{
	agi_log(DEBUG_CRIT, "agi_goto: need to be implemented");
}
?>
