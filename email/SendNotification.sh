#!/bin/bash
curl -s \
	-X POST \
	--user "$MJ_APIKEY_PUBLIC:$MJ_APIKEY_PRIVATE" \
	https://api.mailjet.com/v3.1/send \
	-H 'Content-Type: application/json' \
	-d '{
      "SandboxMode":true,
      "Messages":[
        {
          "From":{
            "Email":"pilot@mailjet.com",
            "Name":"Your Mailjet Pilot"
          },
          "HTMLPart":"<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!",
          "Subject":"Your email flight plan!",
          "TextPart":"Dear passenger, welcome to Mailjet! May the delivery force be with you!",
          "To":[
            {
              "Email":"passenger@mailjet.com",
              "Name":"Passenger 1"
            }
          ]
        }
      ]
	}'
