<?php
//tar -cfl exportedTar.tar db web
//tar -tzf exportedTar.tar>/dev/null
$doThis = "tar -czvf archive.tar";
$addDirs = "";
exec("ls -d */",$lsOutput, $result);

foreach ($lsOutput as $directory){
    $addDirs .= " " . $directory;
    echo "Addding " . $directory . " to tar file\n";
}

$doThis .= $addDirs;

exec($doThis, $output, $result);
echo "\nAfter tar status $result\n";
?>