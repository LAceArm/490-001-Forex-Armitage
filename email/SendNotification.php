<?php
require 'vendor/autoload.php';
use \Mailjet\Resources;
$mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'),true,['version' => 'v3.1']);
$body = [
  'SandboxMode' => true,
  'Messages' => [
    [
      'From' => [
        'Email' => "pilot@mailjet.com",
        'Name' => "Your Mailjet Pilot"
      ],
      'HTMLPart' => "<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!",
      'Subject' => "Your email flight plan!",
      'TextPart' => "Dear passenger, welcome to Mailjet! May the delivery force be with you!",
      'To' => [
        [
          'Email' => "briantheduck259@gmail.com",
          'Name' => "Passenger 1"
        ]
      ]
    ]
  ]
];
$response = $mj->post(Resources::$Email, ['body' => $body]);
$response->success() && var_dump($response->getData());
?>
