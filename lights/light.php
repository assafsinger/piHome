<?php

$code = htmlspecialchars($_GET["code"]);
if (is_numeric($code)){
   $output = exec("sudo /home/assafs/workspace/433Utils/RPi_utils/codesend $code");
   echo "<pre>$output</pre>";
}
?>
