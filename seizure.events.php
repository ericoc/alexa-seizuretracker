<?php

//
// Create a function to add a seizure
//
function add_seizure($api, $user_id, $intent) {
	// TODO: Hit ST API and add a seizure
	// return: true = success false = failed, null = unknown error
}

//
// Create a function to update a seizure (marking it with an end date)
//
function update_seizure($api, $user_id) {

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
function count_seizures($api, $user_id) {

	// TODO: Hit ST API and count seizures today!

	// Return the number of seizures found
	if (is_int($seizure_count)) {
		return $seizure_count;

	// Otherwise, something went wrong trying to count seizures
	} else {
		return null;
	}
}

//
// Create a function to handle a seizure sent from Alexa
//
function handle_seizure ($api, $user_id, $intent) {

	// Add a new seizure, if requested
	if ($intent->name == 'LogSeizure') {

		// Try to add the seizure
		$add_seizure = add_seizure($api, $user_id, $intent);

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
		$update_seizure = update_seizure($api, $user_id, $intent);

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
		$count_seizures = count_seizures($api, $user_id);

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
