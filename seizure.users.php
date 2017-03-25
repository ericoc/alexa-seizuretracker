<?php

// Create a function to find the user in the database based on their Alexa ID
function find_user ($db_link, $alexa_id) {

	$check_user = $db_link->prepare("SELECT `user_id` FROM `users` WHERE `alexa_id` = :alexa_id");
	$check_user->bindValue(':alexa_id', $alexa_id, PDO::PARAM_STR);
	$check_user->execute();
	$result_count = $check_user->rowCount();

	// Found 'em!
	if ($result_count === 1) {

		// Since we found the user, update the database with the time that we last saw them
		$user = $check_user->fetch(PDO::FETCH_ASSOC);
		update_user($db_link, $user['user_id']);

		return $user['user_id'];

	// Otherwise, the user was not found
	} else {
		return null;
	}
}

// Create a function to update a users last seen time
function update_user ($db_link, $user_id) {

	$update_user = $db_link->prepare("UPDATE `users` SET `last_seen` = NOW() WHERE `user_id` = :user_id");
	$update_user->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	if ($update_user->execute()) {
		return true;
	} else {
		return false;
	}
}

// Create a function to create a user in the database based on their Alexa ID
function add_user ($db_link, $alexa_id) {

	$add_user = $db_link->prepare("INSERT INTO `users` VALUES (0, :alexa_id, NOW(), NOW())");
	$add_user->bindValue(':alexa_id', $alexa_id, PDO::PARAM_STR);
	if ($add_user->execute()) {
		return $db_link->lastInsertId();
	} else {
		return null;
	}
}

// Create a base user function that will return a user ID
// ...whether it finds an existing user or creates a new user based on the Alexa ID
function get_user ($alexa_id, $db_link) {

	// Try to find the user by their Alexa user ID
	$find_user = find_user($db_link, $alexa_id);
	if (is_numeric($find_user)) {
		$return = $find_user;

	// Otherwise, add the user if they were not found
	} else {
		$add_user = add_user($db_link, $alexa_id);
		if (is_numeric($add_user)) {
			$return = $add_user;
		} else {
			$return = null;
		}
	}

	// Return whatever was specified
	return $return;
}
