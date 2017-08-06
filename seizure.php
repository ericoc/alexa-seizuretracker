<?php

// Include base Alexa response functions
require_once('alexa.func.php');

// Get the input from Alexa and JSON-decode it
$input = json_decode(file_get_contents("php://input"));

// By default, we always end the session
$end_session = true;

// Proceed if it is a somewhat valid request
if ( (isset($input->session->user->userId)) && (!empty($input->session->user->userId)) && (isset($input->request->intent)) && (isset($input->request->intent->name)) ) {

	// Logging for debugging
	error_log(print_r($input->session->user->accessToken, true));
	error_log(print_r($input->request->intent, true));

	// Tell the user how to track a seizure if they provided no intent
	if ($intent->name == 'AMAZON.HelpIntent') {
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

// Otherwise, tell the user how to track a seizure upon invalid input
} else {
	$message = $default_message;
	$end_session = false;
}

// If $out was not already defined by the account link function, generate it now using $message
if ( (!isset($out)) && (isset($message)) ) {
	error_log($message);
	$out = alexa_out($message, 'SeizureTracker', $message, null, $end_session);
} else {
	error_log('Account is not linked');
}
error_log("\n---\n");

// The output is always JSON, return it either way
header('Content-Type: application/json;charset=UTF-8');
echo "$out\n";
