<?php

// The time zone will be UTC throughout the execution of all PHP
date_default_timezone_set('UTC');

// Include base Alexa response functions
require_once('alexa.func.php');

// Log all HTTP request headers
error_log('--- BEGIN HEADERS ---');
foreach (getallheaders() as $name => $value) {
	error_log($name . ': ' . $value);
}
error_log('--- END HEADERS ---');

// Get the input from Alexa and JSON-decode it before logging it
$raw_input = file_get_contents("php://input");
$input = json_decode($raw_input);
error_log(print_r($input, true));

// Set a default message in case of errors - and we always end the session by default
$default_message = 'Please say, "track a seizure", if you would like to track a seizure.';
$end_session = true;

// Default to not sending a card
$card_content = null;

// Set a failure message immediately if the validation is not completed successfully
if ( (!isset($input->request->timestamp)) || (empty(trim($input->request->timestamp))) || (alexa_validate($raw_input, $input->request->timestamp) !== true) ) {
	http_response_code(400);
	$message = 'Sorry. An invalid request was detected.';
	error_log('INVALID REQUEST DETECTED');

// Handle session ended requests
} elseif ( (isset($input->request->type)) && ($input->request->type === 'SessionEndedRequest') ) {
	$message = null;
	error_log('SESSION ENDED REQUEST RECEIVED');

// Proceed if it is a somewhat valid request
} elseif ( (isset($input->session->user->userId, $input->request->intent, $input->request->intent->name)) && (!empty(trim($input->session->user->userId))) ) {

	// Tell the user how to track a seizure, and allow for a quick response, if they asked for help
	if ($input->request->intent->name == 'AMAZON.HelpIntent') {
		$message = $card_content = 'Thank you for using Seizure Tracker! This skill can be used to track a seizure, to note that a seizure is over, and to count seizures in the past day. Would you like to track a seizure now?';
		$end_session = false;
		error_log('HELP INTENT RECEIVED');

	// Simply end the request if they asked to stop or cancel, or responded "no"
	} elseif ( ($input->request->intent->name == 'AMAZON.CancelIntent') || ($input->request->intent->name == 'AMAZON.StopIntent') || ($input->request->intent->name == 'AMAZON.NoIntent') ) {
		$message = null;
		error_log('STOP/CANCEL INTENT RECEIVED');

	// Tell the user to link their SeizureTracker account if no accessToken was found or their accessToken is not a string
	} elseif ( (!isset($input->session->user->accessToken)) || (empty(trim($input->session->user->accessToken))) || (!is_string($input->session->user->accessToken)) ) {
		$message = 'Sorry, but please go to the Alexa website or app to link your SeizureTracker account.';
		$card = alexa_build_card($message, 'LinkAccount');
		error_log('SENDING LINKACCOUNT CARD');

	// Actually handle the seizure/intent if checks above were okay
	} else {

		// Include required functions and handle the event based on the intent sent from Alexa
		error_log('HANDLING SEIZURE');
		require_once('seizure.events.php');
		$handle_seizure = handle_seizure($input->session->user->accessToken, $input->request->intent);

		// Set the return speech/message awkwardly
		// (TODO: find a better way of doing this)
		if ( (isset($handle_seizure)) && (is_string($handle_seizure)) ) {
			$message = $card_content = $handle_seizure;

		// Otherwise, there was some unknown error
		} else {
			$message = 'Sorry. There was an unknown error.';
		}
	}

// Tell the user how to track a seizure if there was no intent or upon any sort of invalid input - and allow for a quick response
} else {
	$message = $card_content = $default_message;
	$end_session = false;
	error_log('NO INTENT RECEIVED');
}

// Build the card for Alexa
$card = alexa_build_card($card_content);

// Build the final complete JSON response to be sent to Amazon/Alexa including the card and message
$out = alexa_out($message, $card, null, $end_session);

// Log what is being returned
error_log($out);
error_log("\n=====\n");

// The output is always JSON, return it as such
header('Content-Type: application/json;charset=UTF-8');
echo "$out\n";
