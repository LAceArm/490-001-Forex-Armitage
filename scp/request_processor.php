#!/usr/bin/php
<?php
//Version Updating!!!!
//
//11/22/2026
//Three main functions: 1. Get a new updated version. 2. A request to get the new updated version and make that the current version.
//3. Revert to a previous version.
//Because of how I picture this thing working, revert will be done on demand. If a tarball has been corrupted, it will be deleted by the host system 
//and the download will stop.
function scp_requestor($request){
	if(!isset($request["action"])){
		//Do nothing
	}       		
	$action=$request["action"];
	switch($action){ 
		case 

	}

}
?>
