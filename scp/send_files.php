#!/usr/bin/php
<?php
	#Will send files to their respective servers. Front End to Front End, DMZ to DMZ, etc. 
	#Makes heavy use of the shell_exec function to send files via SCP. (How authentication will work is something else)
	#Work flow is as follows:
	# Receive tar ball -> Get message informing the server that the tar ball has been received -> Send tar ball to appropiate location ->
	# Send a Request for the Tar Ball asking the new location to verify download -> New location verifies the tar ball has non-corrupted files
	# -> Return message saying all is good or all is bad. If all is bad the new location will delete the tar ball and the SCP server (this machine	#	) will log the occurence.


?>
