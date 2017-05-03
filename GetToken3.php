<?php
// Create a function to hit the SeizureTracker.com API with a users credentials (username + password) to find that users token
function auth_user ($username, $password) {
	// Include the SeizureTracker API settings
	require_once('st.api.php');
	// Set the SeizureTracker API URL to hit
	$st_api->token_url = $st_api->base_url . '/STUser/GetToken.php/JSON/' . $st_api->access_code . '/' . $st_api->project_id;
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $st_api->token_url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_USERAGENT, $st_api->user_agent);
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

if ((!empty($_POST['st_username'])) OR (!empty($_POST['st_password']))){ 
	
	$renderError = "Please enter a username and password.";
	
	}

// Proceed in handling the below log-in form if it was submitted
if ( (isset($_POST['st_username'])) && (isset($_POST['st_password'])) && (!empty($_POST['st_username'])) && (!empty($_POST['st_password'])) ) {
	// Try to authenticate the user with SeizureTracker.com
	$auth_user = auth_user($_POST['st_username'], $_POST['st_password']);
	// Handle invalid credentials
	if ($auth_user === false) {
		$renderError = "Unfortunately, an invalid user name or password was given. Please go back and try again.";
		//echo 'Unfortunately, an invalid user name or password was given. Please go back and try again.';
	// Handle successful authentication by redirecting the user back to Amazon/Alexa
	} elseif ( (isset($auth_user)) && (is_string($auth_user)) ) {
		$amazon_url = htmlspecialchars($_GET['redirect_uri']) . '#state=' . htmlspecialchars($_GET['state']) . '&access_token=' . $auth_user . '&token_type=Bearer';
		error_log("Successful auth from $auth_user (" . $_POST['st_username'] . ") / sending them to " . $amazon_url . "!");
		header("Location: $amazon_url");
	// Handle unknown errors
	
	//Checks that both username and passwiord has a value
	//} elseif ((!empty($_POST['st_username'])) || (!empty($_POST['st_password']))){ 
	
	//$renderError = "Please enter a username and password.";
	
	} else {
		$renderError = "Unfortunately, there was an unknown error trying to authenticate you to SeizureTracker.com - please try again later. Feel free to contact support@SeizureTracker.com with any questions.";
		//echo 'Unfortunately, there was an unknown error trying to authenticate you to SeizureTracker.com - please try again later.';
	}
} // Removed else here because error are rendered within html and if successful should redriect through header above.  else {
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>

<script type="text/javascript" media="screen"><!--
$('.error-page').hide(0);

$('.login-button , .no-access').click(function(){
  $('.login').slideUp(500);
  $('.error-page').slideDown(1000);
});

$('.try-again').click(function(){
  $('.error-page').hide(0);
  $('.login').slideDown(1000);
});
</script> 
<style type="text/css">
<!--
body {
background:#FFF;
  margin:0px;
  font-family: 'Ubuntu', sans-serif;
	background-size: 100% 110%;
}
h1, h2, h3, h4, h5, h6, a {
  margin:0; padding:0;
}
.login {
  margin:0 auto;
  max-width:500px;
}
.login-header {
  color:#DC143C;
  text-align:center;
  font-size:100%;
}
/* .login-header h1 {
   text-shadow: 0px 5px 15px #000; */
}
.login-form {
  border:.5px solid #DC143C;
  background:#FFF;
  border-radius:10px;
  box-shadow:0px 0px 10px #000;
}
.login-form h3 {
  text-align:left;
  margin-left:40px;
  color:#DC143C;
}
.login-form {
  box-sizing:border-box;
  padding-top:15px;
	padding-bottom:10%;
  margin:5% auto;
  text-align:center;
}
.login input[type="text"],
.login input[type="password"] {
  max-width:400px;
	width: 80%;
  line-height:3em;
  font-family: 'Ubuntu', sans-serif;
  margin:1em 2em;
  border-radius:5px;
  border:2px solid #f2f2f2;
  outline:none;
  padding-left:10px;
}
.login-form input[type="button"] {
  height:30px;
  width:100px;
  background:#fff;
  border:1px solid #DC143C;
  border-radius:20px;
  color: slategrey;
  text-transform:uppercase;
  font-family: 'Ubuntu', sans-serif;
  cursor:pointer;
}
.sign-up{
  color:#666666;
  margin-left:-70%;
  cursor:pointer;
  text-decoration:underline;
}
.no-access {
  color:#999999;
  margin:20px 0px 20px -57%;
  text-decoration:underline;
  cursor:pointer;
}
.try-again {
  color:#f2f2f2;
  text-decoration:underline;
  cursor:pointer;
}
.Error {
	text-align: center;
	padding: 10;
	font-family: 'Ubuntu', sans-serif;
}

/*Media Querie*/
@media only screen and (min-width : 150px) and (max-width : 530px){
  .login-form h3 {
    text-align:center;
    margin:0;
  }
  .sign-up, .no-access {
    margin:10px 0;
  }
  .login-button {
    margin-bottom:10px;
  }
}

img {
    width: 50%;
    height: auto;
}

.headerimage{
	width: 80%;
    height: auto;
	}
	
</style>


</head>

<body>
<link href='http://fonts.googleapis.com/css?family=Ubuntu:500' rel='stylesheet' type='text/css'>
<div>
  <div align="center"><img src="../../STImages/Seizure_Tracker_Logo.jpg" /></div>
</div>
<div class="login">
  <div class="login-header">
    <h1>Connect to Alexa </h1>
  </div>
  <div class="Error">
  <?php //if (isset($renderError)){echo $renderError;} 
  echo $renderError;
  ?>
  </div>
  <form method="post">
  <div class="login-form">
    <h3>Username:</h3>
    <input type="text" placeholder="Seizure Tracker Username" name="st_username"/><br>
    <h3>Password:</h3>
    <input type="password" placeholder="Seizure Tracker Password" name="st_password"/>
    <br>
    <input type="submit" value="Login" class="login-button"/>
    <br>
    <a href="#" class="sign-up">Sign Up!</a>
    <br>
    <h6 class="no-access"><a href="#">Need help accessing your account?</a></h6>
  </div>
  </form>
</div>
</body>
</html>
