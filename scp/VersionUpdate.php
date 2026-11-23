#!/usr/bin/php
<?php
	/*This file will update a file in the Deploys Database. Everytime a VM pushes a new version with some new feature, this 
	 * fille will update a json file to use the new version of the file (1.0, 2.0, 3.0, .etc) as well as the previous version.
	 * If there is a need to revert, the file will also update the current version to be the previous version. The previous version field 
	 * will then be updated manually. */
	require_once __DIR__"/lib/rabbitMQLib.inc";

	$filedb=file_get_contents(__DIR__."/home/laa55-scp-server/Deploys/Versions.json");
	$filepath="/home/laa55-scp-server/Deploys/";
	$datestr=date("Y/m/d H:i:s").": ";

	/* Updates the update field in the Versions json file. Does NOT send new version
		* System needs to be one of the valid JSON names */
	function NewUpdate($versionName,$system){ 
		global $fliedb;
		global $datestr;

		$data=json_decode($filedb,true);
		
		$data[$system]["Update Version File"]=$versionName;

		$new=json_encode($data);

		$flag=$file_put_contents("/home/laa55-scp-server/Deploys/Versions.json",$new);

		if(!$flag){ 
			$msg=$datestr."COULD NOT UPDATE JSON.";
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/SCP.log");
			return array("success"=>false,"Message"=>"Could not  Update");
		}
		return array("success"=>true,"Message"=>"Updated Database");//file put contents returns false on failure.
	}
	
	//Updates the JSON file. Does NOT send an update file
	function UpdateCurrentVersion($system){ 
		global $filedb;
		$data=json_decode($filedb,true);

		$current=$data[$system]["Current Version File"];
		$data[$system]["Current Version File"]=$data[$system]["Update Version File"];
		$data[$system]["Previous Version File"]=$current;
		
		$new=json_encode($data);

		$flag=file_put_contents("/home/laa55-scp-server/Deploys/Versions.json",$new);
		
		if(!$flag){ 
			return $flag;
		}
		return true;
	}

	function GetNewUpdate($system){ 
		global $filedb;
		global $datestr;
		$data=json_decode($filedb,true);

		$update=$data[$system]["Update Version File"];

		//exec("scp /home/laa55-scp-server/Deploys/$system/$update $username@$ip:$directory",$output,$flag);

		$flag=UpdateCurrentVersion($system);

		$destination=$filepath.$update;
		if(!$flag){ 
			$msg=$datestr."COULD NOT UPDATE JSON";
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/SCP.log");
			return array("success"=>false,"Message"=>"File sent, but database not updated. Please update the JSON file accordingly");
		}
		return array("success"=>true, "Message"=>"Database Updated","File Path"=> $destination);
	}

	/*Will overwrite previous version */
	function RevertPrevious($system){ 
		global $filedb;

		$data=json_decode($filedb,true);

		$data[$system]["Current Version File"]=$data[$system]["Previous Version File"];

		$new=json_encode($data);

		$flag=file_put_contents("/home/laa55-scp-server/Deploys/Versions.json",$new);

		if(!$flag){ 
			return false;
		}
		return true;
	}

	function GetPreviousVersion($system){ 
		global $filedb;
		global $datestr;
		global $filepath;
		$data=json_decode($filedb,true);

		$prev=$data[$system]["Previous Version File"];

		//exec("scp /home/laa55-scp-server/Deploys/$system/$previous $username@$ip:$directory",$output,$flag);

		$flag=RevertPrevious($system);

		$file=$filepath.$prev;

		if(!$flag){ 
			$msg=$datestr."COULD NOT UPDATE JSON";
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/SCP.log");
			return array("success"=>false,"Message"=>"Could Not update JSON File. Please update it manually");
		}
		return array("success"=>true,"Message"=>"Sucessfully reverted","File Path"=>$file);
	}

?>
