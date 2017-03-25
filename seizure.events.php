<?php

//
// Create a function to add a seizure
//
function add_seizure($db_link, $user_id, $intent) {

	// Add seizure to database
	$add_seizure = $db_link->prepare("INSERT INTO `seizures` VALUES (0, :user_id, NOW(), NULL)");
	$add_seizure->bindValue(':user_id', $user_id, PDO::PARAM_INT);

	// Return the seizure ID if it was successfully added!
	if ($add_seizure->execute()) {
		return $db_link->lastInsertId();

	// Otherwise, something went wrong adding the seizure
	} else {
		return null;
	}
}

//
// Create a function to update a seizure (marking it with an end date)
//
function update_seizure($db_link, $user_id) {

	// Find the most recent seizure for this user in the past five minutes that has a NULL end date
	$find_seizure = $db_link->prepare("SELECT `seizure_id` FROM `seizures` WHERE `user_id` = :user_id AND `dt_started` > NOW() - INTERVAL 5 MINUTE AND `dt_ended` IS NULL LIMIT 1");
	$find_seizure->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$find_seizure->execute();
	$result_count = $find_seizure->rowCount();

	// Found it, move on with updating the end date
	if ($result_count === 1) {
		$seizure = $find_seizure->fetch(PDO::FETCH_ASSOC);
		$seizure_id = $seizure['seizure_id'];

		// Update the seizures end date to the current time based on the seizures ID that we just got
		$update_seizure = $db_link->prepare("UPDATE `seizures` SET `dt_ended` = NOW() WHERE `seizure_id` = :seizure_id AND `user_id` = :user_id");
		$update_seizure->bindValue(':seizure_id', $seizure_id, PDO::PARAM_INT);
		$update_seizure->bindValue(':user_id', $user_id, PDO::PARAM_INT);

		// Seizure was successfully updated!
		if ($update_seizure->execute()) {
			return true;
		} else {
			return false;
		}

	// Otherwise, no seizure was found to update so this process failed
	} else {
		return null;
	}
}

//
// Create a function to count the number of seizures for a user today
//
function count_seizures($db_link, $user_id) {

	// Find seizures for current user that happened on the current day
	$count_seizures = $db_link->prepare("SELECT `seizure_id` FROM `seizures` WHERE `user_id` = :user_id AND DATE(`dt_started`) = CURDATE()");
	$count_seizures->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	$count_seizures->execute();
	$seizure_count = $count_seizures->rowCount();

	// Return the number of rows (seizures) found
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
function handle_seizure ($db_link, $user_id, $intent) {

	// Add a new seizure, if requested
	if ($intent->name == 'LogSeizure') {

		// Try to add the seizure
		$add_seizure = add_seizure($db_link, $user_id, $intent);

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
		$update_seizure = update_seizure($db_link, $user_id, $intent);

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
		$count_seizures = count_seizures($db_link, $user_id);

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
