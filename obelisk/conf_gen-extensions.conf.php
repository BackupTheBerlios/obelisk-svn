#!/usr/bin/php

<?php

include ('conf_util.inc.php');

echo "
[obelisk-sip-pep]
exten => _X.,1,AGI(".AGI_PATH."/agi_obelisk.php)
exten => _X.,2,hangup();


[obelisk-iax-pep]
exten => _X.,1,AGI(".AGI_PATH."/agi_obelisk.php)
exten -> _X.2,hangup();
";

?>
