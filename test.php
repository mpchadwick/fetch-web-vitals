<?php
// Load the Google API PHP Client Library.
require_once __DIR__ . '/vendor/autoload.php';

$analytics = initializeAnalytics();
$response = getReport($analytics);
printResults($response);


/**
 * Initializes an Analytics Reporting API V4 service object.
 *
 * @return An authorized Analytics Reporting API V4 service object.
 */
function initializeAnalytics()
{

  // Use the developers console and download your service account
  // credentials in JSON format. Place them in this directory or
  // change the key file location if necessary.
  $KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';

  // Create and configure a new client object.
  $client = new Google_Client();
  $client->setApplicationName("Hello Analytics Reporting");
  $client->setAuthConfig($KEY_FILE_LOCATION);
  $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
  $analytics = new Google_Service_AnalyticsReporting($client);

  return $analytics;
}


/**
 * Queries the Analytics Reporting API V4.
 *
 * @param service An authorized Analytics Reporting API V4 service object.
 * @return The Analytics Reporting API V4 response.
 */
function getReport($analytics) {

  // Replace with your view ID, for example XXXX.
  $VIEW_ID = "<XXX>";

  // Create the DateRange object.
  $dateRange = new Google_Service_AnalyticsReporting_DateRange();
  $dateRange->setStartDate("7daysAgo");
  $dateRange->setEndDate("today");

  // Create the Metrics object.
  $sessions = new Google_Service_AnalyticsReporting_Metric();
  $sessions->setExpression("ga:avgEventValue");
  
  // Dimension - Event action
  $eventAction = new Google_Service_AnalyticsReporting_Dimension();
  $eventAction->setName("ga:eventAction");

  // Dimension - Event label
  $eventLabel = new Google_Service_AnalyticsReporting_Dimension();
  $eventLabel->setName("ga:eventLabel");

  // Create the ReportRequest object.
  $request = new Google_Service_AnalyticsReporting_ReportRequest();
  $request->setViewId($VIEW_ID);
  $request->setDateRanges($dateRange);
  $request->setMetrics(array($sessions));
  $request->setDimensions(array($eventAction, $eventLabel));

  // TODO: What if there are more?
  $request->setPageSize(100000);



  $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
  $body->setReportRequests( array( $request) );
  return $analytics->reports->batchGet( $body );
}


/**
 * Parses and prints the Analytics Reporting API V4 response.
 *
 * @param An Analytics Reporting API V4 response.
 */
function printResults($reports) {
  foreach ($reports as $report) {
    
    $header = [];
    foreach ($report->getColumnHeader()->getDimensions() as $dimension) {
      $header[] = $dimension;
    }
    foreach ($report->getColumnHeader()->getMetricHeader()->getMetricHeaderEntries() as $entry) {
      $header[] = $entry->getName();
    }

    echo implode(",", $header) . PHP_EOL;;

    foreach ($report->getData()->getRows() as $row) {
      $data = [];
      foreach ($row->getDimensions() as $dimension) {
        $data[] = $dimension;
      }

      foreach ($row->getMetrics() as $metric) {
        foreach ($metric->getValues() as $value) {
          $data[] = $value;
        }
      }

      echo implode(",", $data) . PHP_EOL;
    }
  }
}