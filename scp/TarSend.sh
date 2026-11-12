# Template file. This will look slightly different in each VM (because what directory is getting sent over will change) but this is what each script
# will do. Tl;dr: Make tar ball of Relevant directory files, SCP it into a separate VM.

#!/bin/bash

tar -cvzf /home/laa55/Project/490-001-octo-fishstick/email email.tar

scp email.tar #some file in the remote machine

# Note: Remote machine will have all ports except 22 and 5672. SCP runs on the same port as SSH, so by allowing SSH into it, it also allows SCP.
