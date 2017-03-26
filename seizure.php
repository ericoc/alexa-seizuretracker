<?php

require_once('alexa.func.php');
require_once('seizure.events.php');

// Get the input from Alexa and JSON-decode it
$input = json_decode(file_get_contents("php://input"));

// Continue with finding the user and handling intent assuming we have somewhat valid input
if ( (isset($input->session->user->userId)) && (!empty($input->session->user->userId)) && (isset($input->request->intent)) && (isset($input->request->intent->name)) ) {

	error_log(print_r($input->session->user->accessToken, true));
	error_log(print_r($input->request->intent, true));

	// Get user "accessToken" from Alexa
	if ( (isset($input->session->user->accessToken)) && (is_string($input->session->user->accessToken)) ) {

		// Handle the event based on the intent sent from Alexa
		$handle_seizure = handle_seizure($input->session->user->accessToken, $input->request->intent);

		// Set the message awkwardly
		// (TODO: find a better way of doing this)
		if ( (isset($handle_seizure)) && (is_string($handle_seizure)) ) {
			$message = $handle_seizure;

		// Otherwise there was an error adding or finding/updating the seizure
		} else {
			$message = 'Sorry. There was an unknown error.';
		}

	// Otherwise, there was an error finding the users token and they probably need to link their account
	} else {
		linkMessage('Sorry, but please go to the Alexa website or app to link your SeizureTracker account.');
		exit;
	}

// Otherwise, invalid input gets a default set of instructions for using this Alexa Skill
} else {
	$message = 'Please say, "Tell SeizureTracker to track a seizure", if you would like to track a seizure.';
}

// The output is always JSON, return it!
header('Content-Type: application/json;charset=UTF-8');
$out = AlexaOut($message, 'SeizureTest', $message);
echo "$out\n";
