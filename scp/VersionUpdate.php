#!/usr/bin/php
<?php
	/*This file will update a file in the Deploys Database. Everytime a VM pushes a new version with some new feature, this 
	 * fille will update a json file to use the new version of the file (1.0, 2.0, 3.0, .etc) as well as the previous version.
	 * If there is a need to revert, the file will also update the current version to be the previous version. The previous version field 
	 * will then be updated manually. */
	function VersionUpdate($versionNumber,$versionName,$system){ 
		$current=file_get_contents(__DIR__."/Versions.json");
		$data=json_decode($current,true);
		$sys=$data[$system];

		$currVer=$sys["Current Version"];
		$currFile=$sys["Current Version File"];

		$data[$system]["Current Version"]=$versionNumber;

		$data[$system]["Current Version File"]=$versionName;

		$data[$system]["Previous Version"]=$currVer;
		$data[$system]["Previous Version File"]=$currFile;

		$update=json_encode($data);
		file_put_contents(__DIR__."/Versions.json",$update);
	}
	function RevertVersion($system){ 
		$currJSON=file_get_contents(__DIR__."/Versions.json");
		$data=json_decode($currJSON,true);
		$prev=$data[$system]["Previous Version"];
		$prevFile=$data[$system]["Previous Version File"];
		$data[$system]["Current Version"]=$prev;
		$data[$system]["Current Version File"]=$prevFile;

		$update=json_encode($data);
		file_put_contents(__DIR__."/Versions.json",$update);
		echo "FILE HAS REVERTED TO A PREVIOUS VERSION. PLEASE UPDATE ACCORDINGLY".PHP_EOL;
	}
?>
