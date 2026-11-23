<?php 
//deballing time
$tarName;
$endDir;

//get cli arguments and put them in variables to be used;
//tar file to extract
if ($argv[1]){
    echo "\nTrying to extract from " . $argv[1];
    $tarName = $argv[1];
} else {
    echo "\nMust specify where to extract from";
    exit;
}
//
if ($argv[2]){
    echo "\nExtracting to " . $argv[2];
    $endDir = " -C " . $argv[2];
}

$doThis = "tar -xvf " . $tarName;

if (isset($endDir)){
    $doThis .= $endDir;
}

exec($doThis, $output, $result);
echo "\nAfter tar status $result\n";

?>