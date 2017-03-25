<?php

// Define a function to create JSON for Alexa to interpret
function AlexaOut ($speech, $card_title, $card_phrase, $reprompt_speech = null, $end_session = true, $redirect_url = null) {

	// Create the outputSpeech array (This is what Alexa says in response)
	$output_speech = array('type' => 'PlainText', 'text' => $speech);

	// Create the card array (This is shown at alexa.amazon.com and within the app)
	$card = array( 'type' => 'Simple', 'title' => $card_title, 'content' => $card_phrase);

	// Create a null (unused/empty) reprompt array
	// (This is used for follow up respones in a proper conversation... I'm not there yet)
	$reprompt = array('outputSpeech' => array('type' => 'PlainText', 'text' => $reprompt_speech));

	// Create final array combining above arrays before it gets turned in to JSON
	$response = array('outputSpeech' => $output_speech, 'card' => $card, 'reprompt' => $reprompt_speech);
	$final = array('version' => '0.1', 'sessionAttributes' => array(), 'response' => $response, 'shouldEndSession' => $end_session);

	// Turn the final array in to JSON and send it back to Amazon/Alexa
	$output = json_encode($final, JSON_PRETTY_PRINT);
	return $output;
}

// Define a function to create JSON for Alexa Flash Briefing skills
function BriefingOut ($uid, $update_date, $title_text, $main_text, $redirect_url = null) {

	// Create the short array to return for Flash Briefing skills, JSON encode it, and return it
	$final = array('uid' => $uid, 'updateDate' => $update_date, 'titleText' => $title_text, 'mainText' => $main_text, 'redirectUrl' => $redirect_url);
	$out = json_encode($final, JSON_PRETTY_PRINT);
	return $out;
}
