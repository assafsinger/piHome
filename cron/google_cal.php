<?php
require_once '/home/assafs/vendor/autoload.php';

/*
first run - should run php google_cal.php
save credentials to /home/assafs/.credentials/calendar-php.json

https://developers.google.com/google-apps/calendar/quickstart/php

*/
#add to crontab:
#
#*/1 * * * * sudo /bin/bash /var/www/html/cron/timer.sh
#*/1 * * * * sudo php /var/www/html/cron/google_cal.php


require_once '/var/www/html/config/dbconfig.inc.php';
require_once '/var/www/html/cron/functions.inc.php';
require_once '/var/www/html/controllers/homeController.php';


define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', '/home/assafs/.credentials/calendar-php.json');
define('CLIENT_SECRET_PATH','/home/assafs/.credentials/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);
$home_controller = new homeController();

// Print the next 10 events on the user's calendar.
$calendarId = 'singer.org.il_biufburoopbjl0esptr15ka6ps@group.calendar.google.com';
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => TRUE,
  'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);

if (count($results->getItems()) == 0) {
  print "No upcoming events found.\n";
} else {
  print "Upcoming events:\n";
  foreach ($results->getItems() as $event) {
    $start = $event->start->dateTime;
    if (empty($start)) {
      $start = $event->start->date;
    }
    $start_date = date_create($start);
    $start = date_format($start_date, 'U');

    $end = $event->end->dateTime;
    if (empty($end)) {
      $end = $event->end->date;
    }
    $end_date = date_create($end);
    $end = date_format($end_date, 'U');

    if (time()>$start && time()<$end){
        $device = getIdForName($event->getSummary());
        $id = $device["id"];
        if ($device["status"] == "0"){
            $home_controller->setActionInternal($id,"timer", $end);
            printf("Device On:%s. Device ID:%s. Will be turned off in %d seconds. \n", $event->getSummary(), $device["id"], $end-time());
        }
    }
  }
}