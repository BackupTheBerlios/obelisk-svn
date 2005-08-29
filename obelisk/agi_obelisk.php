#!/usr/bin/php -q

<?php

include('agi_util.inc.php');

obelisk_dial($extension, $callerId, $callerIdFull);

// fixme cdr-log
?>
