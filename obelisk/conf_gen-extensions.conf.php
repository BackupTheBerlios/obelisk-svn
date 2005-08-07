<?php

echo "
[obelisk-sip-pep]
exten => s,1,AGI(".AGI_PATH."/agi_obelisk.php)


[obelisk-iax-pep]
exten => s,1,AGI(".AGI_PATH."/agi_obelisk.php)
";

?>
