#!/usr/bin/php
<?php
//Version Updating!!!!
function scp_requestor($request){
	if(!isset($request["action"])){
		//Do nothing
	}       		
	$action=$request["action"];
	switch($action){ 
		case "Update":
			if(!isset($request["System Name"]) || !isset($request["File Name"]) || !isset($request["Version Number"])){ 
				return array("Message"=>"Missing Necessary fields","success"=>false);
			}
			$name=$request["System Name"];
			$file=$request["File Name"];
			$num=$request["Version Number"];

			return VersionUpdate($num,$file,$name);
		case "Revert":
			if(!isset($request["System Name"])){ 
				return array("Message" => "Missing Necessary Fields","success"=>false);
			}
			$name=$request["System Name"];

			return RevertVersion($name);

	}

}
?>
