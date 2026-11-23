#!/usr/bin/php
<?php
//Version Updating!!!!
//
//11/22/2026
//Three main functions: 1. Get a new updated version. 2. A request to get the new updated version and make that the current version.
//3. Revert to a previous version.
//Because of how I picture this thing working, revert will be done on demand. If a tarball has been corrupted, it will be deleted by the host system 
//and the download will stop.
require_once __DIR__."/VersionUpdate.php"
function scp_requestor($request){
	if(!isset($request["action"])){
		//Do nothing
	}       		
	$action=$request["action"];
	switch($action){ 
		case "New Update": 
			if(!isset($request["System Name"]) || !isset($request["Version Name"])){ 
				return array("success"=>false,"Message"=> "Missing necessary fields");
			}
			$system=$request["System Name"];
			$versionName=$request["Version Name"];
			return NewUpdate($versionName,$system);
		case "Get New Update":
			$canary= isset($request["System Name"]); //Basically all of these have to be set.
			if(!$canary){ 
				return array("success"=>false,"Message"=>"Missing necessary fields");
			}
			return GetNewUpdate($request["System Name"]);
		case "Get Previous Version":	
			$canary= isset($request["System Name"]); //Basically all of these have to be set.
			if(!$canary){ 
				return array("success"=>false,"Message"=>"Missing necessary fields");
			}
			return GetPreviousVersion($request["System Name"]);

	}

}
?>
