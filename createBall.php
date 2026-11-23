<?php

$doThis;
$addDirs = "";
$verID;
$tarName;

//get cli arguments and put them in variables to be used;
if ($argv[1]){
    echo "\nTar file name will be " . $argv[1] . ".tar.gz";
    $tarName = $argv[1] . ".tar.gz";
} else {
    echo "\nThere must also be a tar file name\n";
    exit;
}

//assign variable with universal cli command to
// -czf c creates new archive, f uses next provided string as archive file name, z compressses with GZip
$doThis = "tar -czf " . $tarName;

//lists all directories within current directory and adds name to an array
exec("ls -d */",$lsOutput, $result);

//adds each directory name as received above to the string command that will build the tar with these directories contents
foreach ($lsOutput as $directory){
    $addDirs .= " " . $directory;
    echo "\nAddding " . $directory . " to tar file";
}

//combines the tar making command with the list of directories string
$doThis .= $addDirs;

//executes the command made above, assigns the output of the command, and assigns the status/result
exec($doThis, $output, $result);

echo "\nAfter tar status $result\n";
?>
