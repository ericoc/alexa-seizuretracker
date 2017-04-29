<?php

//
// Create a function to add a seizure
//
function add_seizure ($api, $user, $intent) {

	// Set the URL for the SeizureTracker events api
	$api->events_url = $api->base_url . '/Events/Events.php/JSON/' . $api->access_code . '/' . $user;

	// Use current timestamp and build the seizure object as JSON
	$timestamp = date('Y-m-d H:i:s');
	$api->seizure->Date_Time = $timestamp;
	$api->seizure->DateTimeEntered = $timestamp;
	$api->seizure->LastUpdated = $timestamp;
	$build_seizure = (object) array('Seizures' => array($api->seizure));
	$seizure_json = json_encode($build_seizure, JSON_PRETTY_PRINT);

	// HTTP request headers for hitting the SeizureTracker API
	$headers = array('Content-type: application/json', 'Content-Length: ' . strlen($seizure_json));

	// Hit the SeizureTracker API to add the seizure
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $api->events_url);
	curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($c, CURLOPT_POSTFIELDS, $seizure_json);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($c, CURLOPT_TIMEOUT, 2);
	curl_setopt($c, CURLOPT_USERPWD, $api->user_name . ':' . $api->pass_code);
	$r = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Proceed in checking that a seizure with the timestamp above actually exists meaning that it was added successfully
	if (isset($r)) {

		// Hit the SeizureTracker API again to retrieve seizures
		// This gives more than we want, but we will check the timestamp later
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $api->events_url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($c, CURLOPT_TIMEOUT, 2);
		curl_setopt($c, CURLOPT_USERPWD, $api->user_name . ':' . $api->pass_code);
		$r = curl_exec($c);
		$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);

		// Proceed if the API responded with a 200 or 201
		if ( ($code === 200) || ($code === 201) ) {

			// Do not bother if the API did not give any seizures back
			if ($r !== 'No events were found in time period.') {

				// Proceed only if the seizures JSON object actually contains items
				$seizures = json_decode($r);
				$seizures = $seizures->Seizures;
				if ( (isset($seizures)) && (!empty($seizures)) ) {

					// Loop through every seizure returned by the API
					foreach ($seizures as $seizure) {

						// If we find a seizure with the timestamp of the one we added, adding a seizure was successful
						if ($seizure->DateTimeEntered === $timestamp) {
							return true;
						}
					}
				}
			}
		}
	}

	// Something weird happened if we haven't returned true by this point
	return null;
}

//
// Create a function to count the number of seizures for a user today
//
function count_seizures ($api, $user) {

	// Set the URL for the SeizureTracker events api
	$api->events_url = $api->base_url . '/Events/Events.php/JSON/' . $api->access_code . '/' . $user;

	// Hit the SeizureTracker API to retrieve seizures
	// This gives more than the current day, but we check their dates later
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $api->events_url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($c, CURLOPT_TIMEOUT, 2);
	curl_setopt($c, CURLOPT_USERPWD, $api->user_name . ':' . $api->pass_code);
	$r = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Seizure count starts at zero
	$seizure_count = (int) 0;

	// Proceed if the API responded with a 200 or 201
	if ( ($code === 200) || ($code === 201) ) {

		// Do not bother if the API did not give any seizures back
		if ($r !== 'No events were found in time period.') {

			// Proceed only if the seizures JSON object actually contains items
			$seizures = json_decode($r);
			$seizures = $seizures->Seizures;
			if ( (isset($seizures)) && (!empty($seizures)) ) {

				// Loop through every seizure returned by the API, but only count the ones that occurred today
				$current_day = date('Y-m-d');
				foreach ($seizures as $seizure) {
					if (strtok($seizure->Date_Time, ' ') === $current_day) {
						$seizure_count++;
					}
				}
			}
		}

	// If the API did not give a 200 or 201, something weird happened
	} else {
		return null;
	}

	// Return seizure_count
	return (int) $seizure_count;

}

//
// Create a function to mark a seizure as having ended
//
function end_seizure ($api, $user) {

/*
	TODO: Hit ST API, find most recent seizure, and either:
			1. update it with an end date OR
			2. delete it, but create a new seizure with the old one's start date and an accurate end date
	return: true = success false = failed to find a seizure to mark over, null = unknown error
*/

}

//
// Create a function to handle a seizure request sent from Alexa
//
function handle_seizure ($user, $intent) {

	// Include the SeizureTracker API settings/credentials
	require_once('.st.api.php');

	// Count users current number of seizures today, if requested
	if ($intent->name == 'CountSeizures') {

		// Get the count of the current users seizures today
		$count_seizures = count_seizures($st_api, $user);

		// All set; return how many seizures were tracked today, using the users own words
		if ($count_seizures > 0) {
			$return = 'So far today, I have tracked ' . $count_seizures . ' ' . $intent->slots->SeizureWords->value;

		// No seizures were found for today
		} elseif ($count_seizures === 0) {
			$return = 'No seizures have been tracked today.';

		// Something went wrong trying to determine seizure count
		} elseif ( ($count_seizures === null) || ($count_seizures < 0) ) {
			$return = 'Sorry. There was an error trying to count the number of seizures that have been tracked today.';
		}

	// Add a new seizure, if requested
	} elseif ($intent->name == 'LogSeizure') {

		// Try to add the seizure
		$add_seizure = add_seizure($st_api, $user, $intent);

		// If it worked and was verified, adding the seizure was successful
		if ($add_seizure === true) {
			$return = 'Okay. The seizure has been tracked.';

		// Otherwise, something went wrong trying to add the seizure
		} else {
			$return = 'Sorry. There was an error tracking the seizure.';
		}

	// Mark a seizure as having ended, if requested
	} elseif ($intent->name == 'EndSeizure') {

		// Try to mark the seizure as having ended
		$end_seizure = end_seizure($st_api, $user, $intent);

		// All set; seizure was updated and marked as over
		if ($end_seizure === true) {
			$return = 'Okay. The seizure has been marked as over.';

		// No seizure could be found to mark as over
		} elseif ($end_seizure === false) {
			$return = 'Sorry. No seizure could be found to mark as over.';

		// No seizure was found to mark over or something weird happened?
		} elseif ($end_seizure === null) {
			$return = 'Sorry. There was an error trying to mark a seizure as over.';
		}
	}

	// Return whatever was specified
	return $return;
}
