#!/usr/bin/php
<?php
	function version_update_qa(){
		//Remember: This is a request. It has to return something	
		$response=array();
		
		// To check if a tar ball is good: Get a list of tar files with tar -tzf using the exec() function. Throw output away
		// To /dev/null. If the third variable returns 0, all is good, otherwise, rm  the tar ball and return an error.
		
		exec("tar -tzf frontdev.tar.gz > /dev/null",$output,$canary);
		
		if($canary==0){ 
			//Do some stuff
			$response["success"]=true;
			return $response;
		}	
		else{ 
			//Ruh roh, Raggy
			exec("rm /home/username/frontdev.tar.gz"); 
			$response["success"]=false;
			return $response; 
		}
	}
	
	function version_update_prod(){
		$response=array();

		exec("tar -tzf frontqa.tar.gz > /dev/null",$output,$canary);

		if($canary==0){ 
			//Do some stuff
			$response["success"]=true;

		}
		else{ 
			exec("rm /home/username/frontqa.tar.gz");
			$response["success"]=false;
			return $response;
		}
	}
?>
