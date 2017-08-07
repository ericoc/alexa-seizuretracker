<?php

// Include base Alexa response functions
require_once('alexa.func.php');

// Log all HTTP request headers
error_log('--- BEGIN HEADERS ---');
foreach (getallheaders() as $name => $value) {
	error_log($name . ' : ' . $value);
}
error_log('--- END HEADERS ---');

// Get the input from Alexa and JSON-decode it before logging it
$raw_input = file_get_contents("php://input");
$input = json_decode($raw_input);
error_log(print_r($input, true));

// Set a default message in case of errors - and we always end the session by default
$default_message = 'Please say, "track a seizure", if you would like to track a seizure.';
$end_session = true;

// Set a failure message immediately if the validation fails
if (alexa_validate($raw_input, $input->request->timestamp) !== true) {
	$message = 'Sorry. An invalid request was detected.';

// Proceed if it is a somewhat valid request
} elseif ( (isset($input->session->user->userId)) && (!empty($input->session->user->userId)) && (isset($input->request->intent)) && (isset($input->request->intent->name)) ) {

	// Tell the user how to track a seizure, and allow for a quick response, if they provided no intent
	if ($input->request->intent->name == 'AMAZON.HelpIntent') {
		$out = $default_message;
		$end_session = false;

	// Tell the user to link their SeizureTracker account if no accessToken was found or their accessToken is not a string
	} elseif ( (!isset($input->session->user->accessToken)) || (empty($input->session->user->accessToken)) || (!is_string($input->session->user->accessToken)) ) {
		$out = alexa_link_out('Sorry, but please go to the Alexa website or app to link your SeizureTracker account.');

	} else {

		// Include required functions and handle the event based on the intent sent from Alexa
		require_once('seizure.events.php');
		$handle_seizure = handle_seizure($input->session->user->accessToken, $input->request->intent);

		// Set the message awkwardly
		// (TODO: find a better way of doing this)
		if ( (isset($handle_seizure)) && (is_string($handle_seizure)) ) {
			$message = $handle_seizure;

		// Otherwise there was some unknown error
		} else {
			$message = 'Sorry. There was an unknown error.';
		}
	}

// Otherwise, tell the user how to track a seizure upon any sort of invalid input and allow for a quick response
} else {
	$message = $default_message;
	$end_session = false;
}

// If $out was not already defined (because account linking was not done), generate it now using $message
if ( (!isset($out)) && (isset($message)) ) {
	$out = alexa_out($message, 'SeizureTracker', $message, null, $end_session);
}

// Log what is being returned
error_log($out);
error_log("\n=====\n");

// The output is always JSON, return it as such
header('Content-Type: application/json;charset=UTF-8');
echo "$out\n";
