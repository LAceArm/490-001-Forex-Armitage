#!/bin/bash

source .env

curl -X POST 'https://api.resend.com/emails' \
     -H 'Authorization: Bearer '$API_KEY \
     -H 'Content-Type: application/json' \
     -d $'{
  "from": "Acme <onboarding@resend.dev>",
  "to": ["delivered@resend.dev"],
  "subject": "hello world",
  "html": "<p>it works!</p>"
}'
