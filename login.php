<?php

// Create a function to hit the SeizureTracker.com API with a users credentials (username + password) to find that users token
function auth_user ($username, $password) {

	// Include the SeizureTracker API settings
	require_once('.st.api.php');

	// Set the SeizureTracker API URL to hit
	$st_api->tokenurl = $st_api->baseurl . '/STUser/GetToken.php/JSON/' . $st_api->accesscode . '/' . $st_api->projectid;

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $st_api->tokenurl);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_USERAGENT, 'Alexa Authentication Development 1.0 / https://github.com/ericoc/alexa-seizuretracker');
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($c, CURLOPT_TIMEOUT, 2);
	curl_setopt($c, CURLOPT_USERPWD, $username . ':' . $password);
	$r = json_decode(curl_exec($c));
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	// Proceed if the API responded with a 200 or 201
	if ( ($code === 200) || ($code === 201) ) {

		// Return the user token if valid credentials found an active user
		if ( (isset($r->UserRelationshipToken)) && (!empty($r->UserRelationshipToken)) && (isset($r->Status)) && ($r->Status === 'Active') ) {
			return $r->UserRelationshipToken;

		// Otherwise, if it was a successful request, but no user token exists, there was some strange problem
		} else {
			return null;
		}

	// A 401 HTTP response means invalid username/password
	} elseif ($code === 401) {
		return false;

	// Last resort is that there was some kind of problem reaching the API?
	} else {
		return null;
	}
}

// Proceed in handling the below form if it was submitted
if ( (isset($_POST['st_username'])) && (isset($_POST['st_password'])) && (!empty($_POST['st_username'])) && (!empty($_POST['st_password'])) ) {

	// Try to authenticate the user with SeizureTracker.com
	$auth_user = auth_user($_POST['st_username'], $_POST['st_password']);

	// Handle invalid credentials
	if ($auth_user === false) {
		echo 'Unfortunately, an invalid user name or password was given. Please go back and try again.';

	// Handle successful authentication by redirecting the user back to Amazon/Alexa
	} elseif ( (isset($auth_user)) && (is_string($auth_user)) ) {
		$amazon_url = htmlspecialchars($_GET['redirect_uri']) . '#state=' . htmlspecialchars($_GET['state']) . '&access_token=' . $auth_user . '&token_type=Bearer';
		header("Location: $amazon_url");

	// Handle unknown errors
	} else {
		echo 'Unfortunately, there was an unknown error trying to authenticate you to SeizureTracker.com - please try again later.';
	}

} else {
?>
<form method="post">
<b><u>Log in to (Test.)SeizureTracker.com</u></b><br><br>
User name: <input type="text" name="st_username"><br>
Password: <input type="password" name="st_password"><br>
<input type="submit" value="Log in">
</form>
<?php
}
