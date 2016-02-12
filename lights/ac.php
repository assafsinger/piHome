<?php

$output = 1;
$command = htmlspecialchars($_GET["command"]);
if (strcmp("ON", $command) == 0){
   $output = exec("irsend SEND_ONCE ac1 ON_HEAT");
}
if (strcmp("OFF", $command) == 0){
   $output = exec("irsend SEND_ONCE ac1 OFF");
}

echo "<pre>$output</pre>";
?>
