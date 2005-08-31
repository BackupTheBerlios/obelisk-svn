<?php

/*
 * obelisk include this file when it needs to dial an extension with 
 * this module and then call sample_dial.
 */


/*
 * PRE :
 *	$extension : the extension to dial
 *	$callerId : the callerId extension
 *	$callerIdFull : complete caller Id
 *	#include agi_util.inc.php
 *
 * POST :
 *	the extension is dialed or agi_notFound();
 *	if successfull, return a positive number or 0 which is the price of
 *		the call
 *	else return a negative number
 *	
 */
function sample_dial($extension, $callerId, $callerIdFull)
{
	agi_log(DEBUG_ERR, "sample/dial.inc.php: Not yet implemented");
	return agi_notFound($extension, $callerId, $callerIdFull);
}

?>
