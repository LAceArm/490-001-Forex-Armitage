#!/usr/bin/php
<?php
//Note: this is a published message eater. It will do things when it processes a published message. It will not return ***anything***. 
function scp_requestor($request){
	if(isset($request["from"]){
		//Do nothing
	}       		
	$from=$request["from"];
	switch($from){ 
		case "FrontDev":
			//Send file to QA, Send message to process it
		case "BackDev":
			//Ditto
		case "DMZDev":
			//Ditto
		case "FrontQA":
			//Send file to Prod. Send message to process it
		case "BackQA":
			//Ditto
		case "DMZQA":
			//Ditto
		default:
			//Who the hell is this guy? 
		     	break;
	}

}
?>
