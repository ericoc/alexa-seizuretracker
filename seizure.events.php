<?php

//
// Create a function to add a seizure
//
function add_seizure ($api, $user, $intent) {
	// TODO: Hit ST API and add a seizure
	// return: true = success false = failed, null = unknown error
}

//
// Create a function to update a seizure (marking it with an end date)
//
function update_seizure ($api, $user) {

/*
	TODO: Hit ST API, find most recent seizure, and either:
			1. update it with an end date OR
			2. delete it, but create a new seizure with the old one's start date and an accurate end date
	return: true = success false = failed, null = unknown error
*/

}

//
// Create a function to count the number of seizures for a user today
//
function count_seizures ($api, $user) {

	// Set the URL for the SeizureTracker events api
	$api->eventsurl = $api->baseurl . '/Events/Events.php/JSON/' . $api->accesscode . '/' . $user;

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $api->eventsurl);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_USERAGENT, 'Alexa Authentication Development 1.0 / https://github.com/ericoc/alexa-seizuretracker');
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($c, CURLOPT_TIMEOUT, 2);
	curl_setopt($c, CURLOPT_USERPWD, $api->username . ':' . $api->passcode);
	$r = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Seizure count starts at zero
	$seizure_count = (int) 0;

	// No seizures found
	if ($r === 'No events were found in time period.') {
		return $seizure_count;

	// Proceed if the API responded with a 200 or 201
	} elseif ( ($code === 200) || ($code === 201) ) {

		// Proceed if seizures were found
		$seizures = json_decode($r);
		error_log(print_r($seizures,true));
		if ( (isset($seizures)) && (!empty($seizures)) ) {

			// Loop through every seizure returned by the API only counting the ones that occurred today
			$current_day = date('Y-m-d');
			foreach ($seizures as $seizure) {
				error_log(print_r($seizure,true));
				if (strtok($seizure->Date_Time, ' ') === $current_day) {
					error_log('counting this...');
					$seizure_count++;
				}
			}
		}

	// Last resort is that there was some kind of problem reaching the API?
	} else {
		return null;
	}
}

//
// Create a function to handle a seizure sent from Alexa
//
function handle_seizure ($user, $intent) {

	// Include the SeizureTracker API settings/credentials
	require_once('.st.api.php');

	// Add a new seizure, if requested
	if ($intent->name == 'LogSeizure') {

		// Try to add the seizure
		$add_seizure = add_seizure($st_api, $user, $intent);

		// If we got an ID back, it is numeric so adding the seizure was successful and we pass that along
		if (is_numeric($add_seizure)) {
			$return = 'Okay. The seizure has been tracked.';

		// Something went wrong trying to add the seizure
		} elseif ($add_seizure === null) {
			$return = 'Sorry. There was an error tracking the seizure.';
		}

	// Update the end date of a seizure that is over, if requested
	} elseif ($intent->name == 'UpdateSeizure') {

		// Try to update the seizure
		$update_seizure = update_seizure($st_api, $user, $intent);

		// All set; seizure was updated and marked as over
		if ($update_seizure === true) {
			$return = 'Okay. The seizure has been marked as over.';

		// Something went wrong trying to update the seizure
		} elseif ($update_seizure === false) {
			$return = 'Sorry. There was an error while trying to mark the seizure as over.';

		// No seizure was found (in the past five minutes for this user) to mark over
		} elseif ($update_seizure === null) {
			$return = 'Sorry. No seizure could be found to mark as over.';
		}

	// Count users current number of seizures today, if requested
	} elseif ($intent->name == 'CountSeizures') {

		// Get the count of the current users seizures today
		$count_seizures = count_seizures($st_api, $user);

		// All set; return how many seizures were tracked today, using the users own words
		if ($count_seizures > 0) {
			$return = 'So far today, I have tracked ' . $count_seizures . ' ' . $intent->slots->Things->value;

		// No seizures were found for today
		} elseif ($count_seizures === 0) {
			$return = 'No seizures have been tracked today.';

		// Something went wrong trying to determine seizure count
		} elseif ( ($count_seizures === null) || ($count_seizures < 0) ) {
			$return = 'Sorry. There was an error trying to count the number of seizures that have been tracked today.';
		}
	}

	// Return whatever was specified
	return $return;
}
