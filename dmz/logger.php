<?php

function writeLog($message)
{
    $logFile = __DIR__ . "/logs/dmz.log";

    $line = date("Y-m-d H:i:s") . " - " . $message . PHP_EOL;

    file_put_contents($logFile, $line, FILE_APPEND);
}
?>
