<?php

// Define a function to validate Alexa HTTP requests
function alexa_validate ($raw_input, $timestamp) {

	// Immediately fail if either of the two required HTTP headers ("Signature" and "SignatureCertChainUrl") are missing
	if ( (!isset($_SERVER['HTTP_SIGNATURE'])) || (!isset($_SERVER['HTTP_SIGNATURECERTCHAINURL'])) ) {
		$return = false;

	// Validate that the timestamp value in the Alexa HTTP request is recent
	} elseif (alexa_validate_timestamp($timestamp) !== true) {
		$return = false;

	// Validate the Alexa SignatureCertChainUrl HTTP request header value
	} elseif (alexa_validate_signature_url($_SERVER['HTTP_SIGNATURECERTCHAINURL']) !== true) {
		$return = false;

	// Validate the contents of the SignatureCertChainUrl
	} elseif (alexa_validate_signature($raw_input, $_SERVER['HTTP_SIGNATURE'], $_SERVER['HTTP_SIGNATURECERTCHAINURL']) !== true) {
		$return = false;

	// Return true if all above checks pass
	} else {
		$return = true;
	}

	// Set the HTTP response code to 400 if validation failed
	if ($return === false) {
		http_response_code(400);
	}

	return $return;
}

// Define a function to validate that the timestamp within the Alexa HTTP request is recent
function alexa_validate_timestamp ($timestamp) {

	// Ensure that the timestamp from within the HTTP request JSON is within the past minute
	if (time() - strtotime($timestamp) > 60) {
		return false;
	} else {
		return true;
	}
}

// Define a function to validate the Alexa SignatureCertChainUrl HTTP request header value
function alexa_validate_signature_url ($url) {

	// Parse the SignatureCertChainUrl HTTP header URL in to pieces
	$url_pieces = parse_url($url);

	// Ensure the URL host is "s3.amazonaws.com"
	if (strcasecmp($url_pieces['host'], 's3.amazonaws.com') != 0) {
		return false;

	// Ensure the URL path contains "echo.api"
	} elseif (strpos($url_pieces['path'], '/echo.api/') !== 0) {
		return false;

	// Ensure the URL schema is "https"
	} elseif (strcasecmp($url_pieces['scheme'], 'https') != 0) {
		return false;

	// Ensure the URL port is 443 if a port is included
	} elseif ( (array_key_exists('port', $url_pieces)) && ($url_pieces['port'] != '443') ) {
		return false;

	// Return true if all above checks pass
	} else {
		return true;
	}
}

// Define a function to validate the Alexa SignatureCertChainUrl contents
function alexa_validate_signature ($raw_input, $signature, $url) {

	// Download/get the certificate and check it
	$contents = file_get_contents($url);
	if ( (isset($contents)) && (!empty($contents)) ) {
		$ssl_check = openssl_verify($raw_input, base64_decode($signature), $contents, 'sha1');
	}

	// Proceed if the SSL certificate check was successful
	if ( (isset($ssl_check)) && (!empty($ssl_check)) && ($ssl_check == 1) ) {

		// Parse the certificate and ensure it was parsed successfully
		$certificate = openssl_x509_parse($contents);
		if ( (!isset($certificate)) || (empty($certificate)) || (!$certificate) ) {
			return false;

		// Ensure "echo-api.amazon.com" is present within the certificate SubjectAltName
		} elseif (strpos($certificate['extensions']['subjectAltName'], 'echo-api.amazon.com') === false) {
			return false;
		}

		// Get the current time, as well as the valid dates of the certificate
		$time = time();
		$valid_from = $certificate['validFrom_time_t'];
		$valid_to = $certificate['validTo_time_t'];

		// Ensure that the certificate is valid based on time/expiration
		if ( (isset($valid_from, $valid_to)) && (!empty($valid_from)) && (!empty($valid_to)) ) {


			// Fail if the current time is less than the Valid From date of the (early-used?) certificate
			// ... or if the current time is greater than the Valid To date of the (expired) certificate
			if ( ($time < $valid_from) || ($time > $valid_to) ) {
				return false;
			}

		// Fail if the valid from/to dates are missing or empty
		} else {
			return false;
		}

	// Fail if the SSL check does not pass
	} else {
		error_log('SSL check failed:' . openssl_error_string());
		return false;
	}

	// Return true indicating success if we made it this far
	return true;
}

// Define a function to create JSON for Alexa to interpret
function alexa_out ($speech, $card_title, $card_phrase, $reprompt_speech = null, $end_session = true, $redirect_url = null) {

	// Create the outputSpeech array (This is what Alexa says in response)
	$output_speech = array('type' => 'PlainText', 'text' => $speech);

	// Create the card array (This is shown at alexa.amazon.com and within the app)
	$card = array( 'type' => 'Simple', 'title' => $card_title, 'content' => $card_phrase);

	// Create a null (unused/empty) reprompt array
	// (This is used for follow up respones in a proper conversation... I'm not there yet)
	$reprompt = array('outputSpeech' => array('type' => 'PlainText', 'text' => $reprompt_speech));

	// Create final array combining above arrays before it gets turned in to JSON
	$response = array('outputSpeech' => $output_speech, 'card' => $card, 'reprompt' => $reprompt_speech, 'shouldEndSession' => $end_session);
	$final = array('version' => '0.1', 'sessionAttributes' => array(), 'response' => $response);

	// Turn the final array in to JSON and send it back to Amazon/Alexa
	$output = json_encode($final, JSON_PRETTY_PRINT);
	return $output;
}

// Define a function to create JSON for an Alexa card and instructions to users who have not linked their account
function alexa_link_out ($speech) {

	// Create the outputSpeech array (This is what Alexa says in response)
	$output_speech = array('type' => 'PlainText', 'text' => $speech);

	// Create the card array (This is shown at alexa.amazon.com and within the app)
	$card = array('type' => 'LinkAccount');

	// Create final arrays
	$response = array('outputSpeech' => $output_speech, 'card' => $card,);
	$final = array('version' => '0.1', 'sessionAttributes' => array(), 'response' => $response);
	$output = json_encode($final, JSON_PRETTY_PRINT);
	return $output;
}
