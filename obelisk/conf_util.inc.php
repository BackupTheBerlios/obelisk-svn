<?

include_once('common.inc.php');

/**
 * agi_log - write some debug information on stderr
 *
 * * accept lelve between 1 and 4, the level 0 write the message with the
 *	level 1 and then kill the script
 *
 * PRE: 0 <= $level <= 16 ^ strlen($msg) >= 1
 
 */
function conf_log($level, $msg)
{
	$stderr = fopen("php://stderr", "w");

	if ($level &  DEBUG_LEVEL)
		fputs($stderr, $msg);
	
	if ($level == 0)
	{
		agi_terminate(-1);
	}
}

?>
