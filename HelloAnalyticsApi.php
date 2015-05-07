<?php

//de benodigde PHP files uit de project map worden opgeroepen om gebruikt te kunnen worden in deze PHP file
require_once '/src/Google_Client.php';
require_once '/src/contrib/Google_AnalyticsService.php';
// een nieuwe sessie word gestart
session_start();
//er word een object gecreeërd vanuit een class uit de Google_Client.php file

$client = new Google_Client();
//Er word een method gebruikt voor het object $client wat we hierboven hadden aangemaakt
$client->setApplicationName('Hello Analytics API Sample');

// Bezoek https://console.developers.google.com/ om de 
// client id, client secret, en de redirect uri te registreren.

// de variabele $client gebruikt methods uit de Google_client.php file en stelt verschillende parameters vast
$client->setClientId('616341945081-7eip1k4o9iibfifo4rhiikrkjhp27nk5.apps.googleusercontent.com');
$client->setClientSecret('Pm9jopT_O4QUVwG8ElbR7l8n');
$client->setRedirectUri('http://localhost/apidash360/HelloAnalyticsApi.php');
$client->setDeveloperKey('AIzaSyBDJ5penbm2hdwD2xcTD0ulbkjmhVxk12c');
$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly', 'https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile'));

//Magie. Krijgt objecten terug van de Analytics Service in plaats van associatieve arrays.
$client->setUseObjects(true);

if (isset($_GET['code'])) {
  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}


if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if (!$client->getAccessToken()) {
  $authUrl = $client->createAuthUrl();
  print "<a class='login' href='$authUrl'>Connect Me!</a>";

} else {
  // Create analytics service object. See next step below.
	$analytics = new Google_AnalyticsService($client);
runMainDemo($analytics);

}


function runMainDemo(&$analytics) {
  try {
    // Step 2. Get the user's first view (profile) ID.
    $profileIds = getProfileIds($analytics);
    var_dump($profileIds);
    foreach ($profileIds as $profileId) {
    	$profileId = $profileId->getId();
    	var_dump($profileId);
    }

    if (isset($profileId)) {

      // Step 3. Query the Core Reporting API.
      $results = getResults($analytics, $profileId);

      // Step 4. Output the results.
      printResults($results);
    }

  } catch (apiServiceException $e) {
    // Error from the API.
    print 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage();

  } catch (Exception $e) {
    print 'There wan a general error : ' . $e->getMessage();
  }
}


function getFirstprofileId(&$analytics) {
  $accounts = $analytics->management_accounts->listManagementAccounts();




  if (count($accounts->getItems()) > 0) {
    $items = $accounts->getItems();
    $firstAccountId = $items[0]->getId();

    $webproperties = $analytics->management_webproperties
        ->listManagementWebproperties($firstAccountId);

    if (count($webproperties->getItems()) > 0) {
      $items = $webproperties->getItems();
      $firstWebpropertyId = $items[0]->getId();

      $profiles = $analytics->management_profiles
          ->listManagementProfiles($firstAccountId, $firstWebpropertyId);

      if (count($profiles->getItems()) > 0) {
        $items = $profiles->getItems();
        return $items[0]->getId();

      } else {
        throw new Exception('No views (profiles) found for this user.');
      }
    } else {
      throw new Exception('No webproperties found for this user.');
    }
  } else {
    throw new Exception('No accounts found for this user.');
  }
}

function getProfileIds(&$analytics) {
  $accounts = $analytics->management_accounts->listManagementAccounts();




  if (count($accounts->getItems()) > 0) {
    $items = $accounts->getItems();
    var_dump($items); die();
    $firstAccountId = $items[0]->getId();

    $webproperties = $analytics->management_webproperties
        ->listManagementWebproperties($firstAccountId);

    if (count($webproperties->getItems()) > 0) {
      $items = $webproperties->getItems();
      var_dump($items); die();
      $firstWebpropertyId = $items[0]->getId();

      $profiles = $analytics->management_profiles
          ->listManagementProfiles($firstAccountId, $firstWebpropertyId);

      if (count($profiles->getItems()) > 0) {
        $items = $profiles->getItems();
        return $items;

      } else {
        throw new Exception('No views (profiles) found for this user.');
      }
    } else {
      throw new Exception('No webproperties found for this user.');
    }
  } else {
    throw new Exception('No accounts found for this user.');
  }
}

//resultaten ophalen
function getResults(&$analytics, $profileId) {
   return $analytics->data_ga->get(
       'ga:' . $profileId,
       '2015-03-03',
       '2015-04-04',
       'ga:sessions');
}

function printResults(&$results) {
  if (count($results->getRows()) > 0) {
    $profileName = $results->getProfileInfo()->getProfileName();
    $rows = $results->getRows();
    $sessions = $rows[0][0];

    print "<p>First view (profile) found: $profileName</p>";
    print "<p>Total sessions: $sessions</p>";

  } else {
    print '<p>No results found.</p>';
  }
}

//Query the core reporting API for Data



?>