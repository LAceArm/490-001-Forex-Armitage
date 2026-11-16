#!/usr/bin/php
<?php
	#Will send files to their respective servers. Front End to Front End, DMZ to DMZ, etc. 
	#Makes heavy use of the shell_exec function to send files via SCP. (How authentication will work is something else)
	#Work flow is as follows:
	# Receive tar ball -> Get message informing the server that the tar ball has been received -> Send tar ball to appropiate location ->
	# Send a Request for the Tar Ball asking the new location to verify download -> New location verifies the tar ball has non-corrupted files
	# -> Return message saying all is good or all is bad. If all is bad the new location will delete the tar ball and the SCP server (this machine	      #	) will log the occurence.
	
	$daystr=date("Y/m/d H:i:s").": ";
	function FrontDevtoFrontQA(){ 
		//Assumes name will not change.
		exec("scp frontqa.tar.gz username@ipadd:/home/username/",$output,$code);
		//SCP file to another machine
		if($code==0){ 
			$msg=$daystr."FILE FORWARDED SUCCESSFULLY".PHP_EOL;
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/File.log");
		}
		else{ 
			$msg=$daystr."THERE WAS AN ERROR".PHP_EOL;
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/File.log");
			return;
		}
		$request=array();

		$request["Message"]="New Version has been sent";
		$request["type"]="Version Update";
		//Send_request to Appropiate Machine.
		//Whether it returns true or false it does not matter. 
		//Log the occurrence
		if($request["success"]){
			$msg=$daystr."NEW VERSION DOWNLOADED SUCCESSFULLY".PHP_EOL;
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/File.log");
		} 
		else{
			$msg=$daystr."NEW VERSION COULD NOT BE DOWNLOADED".PHP_EOL;
			error_log($msg,3,"/home/laa55-scp-server/WebsiteLogs/File.log");	
		}
	}
?>
