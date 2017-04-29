<?php

// Get the input from Alexa and JSON-decode it
$input = json_decode(file_get_contents("php://input"));

// Proceed if it is a somewhat valid request
if ( (isset($input->session->user->userId)) && (!empty($input->session->user->userId)) && (isset($input->request->intent)) && (isset($input->request->intent->name)) ) {

	// Tell the user to link their SeizureTracker account if no accessToken was found or their accessToken is not a string
	if ( (!isset($input->session->user->accessToken)) || (empty($input->session->user->accessToken)) || (!is_string($input->session->user->accessToken)) ) {
		linkMessage('Sorry, but please go to the Alexa website or app to link your SeizureTracker account.');

	} else {

		// Logging for debugging
		error_log(print_r($input->session->user->accessToken, true));
		error_log(print_r($input->request->intent, true));

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
	$message = 'Please say, "Tell SeizureTracker to track a seizure", if you would like to track a seizure.';
}

// Include base Alexa response functions
require_once('alexa.func.php');

// The output is always JSON, return it!
header('Content-Type: application/json;charset=UTF-8');
$out = AlexaOut($message, 'SeizureTracker', $message);
echo "$out\n";
