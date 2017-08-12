<?php

///
// Create a function to calculate the length of a seizure using its entered time vs. the current time
//
function calculate_seizure_length ($start, $end) {

	// Simply create two DateTime objects, diff them, and return the diff object
	$start_dt = new DateTime($start);
	$end_dt = new DateTime($end);
	$length = $start_dt->diff($end_dt);
	return $length;
}

//
// Create a function to get a users latest seizure
//  which returns one of the following:
//   1. the seizure object
//   2. false - if no seizure was found in the past 48 hours
//   3. null - if there was an API issue
//
function get_latest_seizure ($api, $user) {

	// Set the URL for the SeizureTracker latest event API
	error_log('GETTING LATEST SEIZURE');
	$api->latest_event_url = $api->base_url . '/Events/LastOpenEvent.php/JSON/' . $api->access_code . '/' . $user . '/';

	// Hit the SeizureTracker API to find the users latest seizure
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $api->latest_event_url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, $api->returnxfer);
	curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_TIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_USERPWD, $api->user_name . ':' . $api->pass_code);
	$r = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Return false if no latest seizure was returned
	if ( ($code === 201) && ($r === 'No open events were found in the last 24 hours.') ) {
		return false;
	}

	// Proceed only if the latest seizure JSON object actually contains an item
	$seizure = json_decode($r);
	if ( (isset($seizure)) && (!empty($seizure)) ) {
		$seizure = $seizure->LastOpenSeizure[0];
		error_log(print_r($seizure, true));
		return $seizure;
	}

	// If we got to this point, something went wrong
	return null;
}

//
// Create a function to add a seizure
//
function add_seizure ($api, $user) {

	// Set the URL for the SeizureTracker events API
	$api->events_url = $api->base_url . '/Events/Events.php/JSON/' . $api->access_code . '/' . $user;

	// Use current timestamp and build the seizure object as JSON
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
	curl_setopt($c, CURLOPT_RETURNTRANSFER, $api->returnxfer);
	curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_TIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_USERPWD, $api->user_name . ':' . $api->pass_code);
	$r = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Proceed in checking that a seizure with the current timestamp actually exists meaning that it was added successfully
	if (isset($r)) {

		// Hit the SeizureTracker API again to retrieve the latest seizure to confirm the seizure addition
		$latest_seizure = get_latest_seizure($api, $user);
		if (isset($latest_seizure)) {

			// If the latest seizures timestamp matches, everything worked
			if ( (is_object($latest_seizure)) && ($latest_seizure->DateTimeEntered === $api->timestamp) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	// If we got to this point, something went wrong
	return null;
}

//
// Create a function to count the number of seizures for a user today
//
function count_seizures ($api, $user) {

	// Set the URL for the SeizureTracker events API
	$api->events_url = $api->base_url . '/Events/Events.php/JSON/' . $api->access_code . '/' . $user;

	// Append the URL parameters to request only the seizures for today from the API
	$current_day = date('Y-m-d');
	$api->events_url .= '/?Length=DateRange&Date=' . $current_day . '&StartDate=' . $current_day;

	// Hit the SeizureTracker API to retrieve seizures
	// This gives more than the current day, but we check their dates later
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $api->events_url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, $api->returnxfer);
	curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_TIMEOUT, $api->timeout);
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
				$seizure_count = count($seizures);
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

	// Hit the SeizureTracker API to retrieve the latest seizure so that we can mark it as over
	$latest_seizure = get_latest_seizure($api, $user);

	// If no object was found, there were no seizures found recently to mark as being over so do not bother continuing
	if ( (isset($latest_seizure)) && (!is_object($latest_seizure)) ) {
		return false;

	// Otherwise, begin modifying the object for the latest seizure so it can be updated and marked as over
	} else {
		$update_seizure = $latest_seizure;
	}

	// Calculate the length of the seizure to update it when marking the event as over
	$seizure_length = calculate_seizure_length($latest_seizure->DateTimeEntered, $api->timestamp);
	$update_seizure->length_sec = $seizure_length->s;
	$update_seizure->length_min = $seizure_length->m;
	$update_seizure->length_hr = $seizure_length->h;

	// Fix the "LastUpdated" timestamp within the seizure object
	$update_seizure->LastUpdated = $api->timestamp;

	// Build the updated seizure object as JSON
	$build_seizure = (object) array('Seizures' => array($update_seizure));
	$seizure_json = json_encode($build_seizure, JSON_PRETTY_PRINT);

	// HTTP request headers for hitting the SeizureTracker API
	$headers = array('Content-type: application/json', 'Content-Length: ' . strlen($seizure_json));

	// Hit the SeizureTracker API to mark the seizure as over
	$api->events_url = $api->base_url . '/Events/Events.php/JSON/' . $api->access_code . '/' . $user;
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $api->events_url);
	curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($c, CURLOPT_POSTFIELDS, $seizure_json);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, $api->returnxfer);
	curl_setopt($c, CURLOPT_USERAGENT, $api->user_agent);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_TIMEOUT, $api->timeout);
	curl_setopt($c, CURLOPT_USERPWD, $api->user_name . ':' . $api->pass_code);
	$r = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Proceed in checking that the seizure was successfully marked as over
	if ( ($code === 202) || ($r === '1 events have been edited on your SeizureTracker.com account.') ) {
		return true;
	} else {
		error_log("ENDING SEIZURE FAILED: ($code) $r");
	}

	// If we got to this point, something went wrong
	return null;
}

//
// Create a function to handle a seizure request sent from Alexa
//
function handle_seizure ($user, $intent, $timestamp) {

	// Include the SeizureTracker API settings/credentials
	require_once('st.api.php');

	// Count users current number of seizures today, if requested
	if ($intent->name == 'CountSeizures') {

		// Get the count of the current users seizures today
		error_log('COUNTING SEIZURES');
		$count_seizures = count_seizures($st_api, $user);

		// All set; return how many seizures were tracked today, using the users own words
		if ($count_seizures > 0) {

			// Try to respond using the users own phrase for the count, but if not possible, go with "seizures"
			if (isset($intent->slots->SeizureWords->value)) {
				$counted_word = $intent->slots->SeizureWords->value;
			} else {
				$counted_word = 'seizures';
			}

			$return = "So far today, $count_seizures $counted_word have been tracked.";

		// No seizures were found for today
		} elseif ($count_seizures === 0) {
			$return = 'No seizures have been tracked today.';

		// Something went wrong trying to determine seizure count
		} elseif ( ($count_seizures === null) || ($count_seizures < 0) ) {
			$return = 'Sorry. There was an error trying to count the number of seizures that have been tracked today.';
		}

	// Add a new seizure, if requested
	} elseif ( ($intent->name == 'AddSeizure') || ($intent->name == 'AMAZON.YesIntent') ) {

		// Try to add the seizure
		error_log('ADDING SEIZURE');
		$add_seizure = add_seizure($st_api, $user);

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
		error_log('ENDING SEIZURE');
		$end_seizure = end_seizure($st_api, $user);

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
