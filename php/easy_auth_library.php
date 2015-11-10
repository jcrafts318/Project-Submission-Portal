<?php
/* File Name:           easy_auth_library.php
 * Description:         This file contains library functions directly called by client processes
 * Dependencies:        easy_auth_toolbox.php
 * Additional Notes:    none
 */

require_once("easy_auth_toolbox.php");

function CreateUser($database, $email, $password)
// PRE:  $database is an open MySQL database connection
//       $email and $password have already been validated by calling validate.php
// POST: a new user is created and an email is sent to confirm the user's account
{
	if (!ConfirmedUserExists($database, $email))
	{
		$email = SanitizeString($email);
		$password = SanitizeString($password);
		$salt = GenerateSalt();
		$confirmVector = GenerateSalt();			// random vector to hash with salt for confirm code
		$confirmCode = HashPassword($confirmVector, $salt);	// confirm code is hashed value of salt plus random vector
		$hash = HashPassword($password, $salt);
		$query = "INSERT INTO users_unconfirmed SET email='$email', pw_hash='$hash', pw_salt='$salt', confirm_code='$confirmCode'";
		$retval = MySqlDatabaseQuery($database, $query, TRUE);
		SendConfirmation($database, $email);
		return $retval;
	}
	else
	{
		return FALSE;
	}
}

function AuthenticateUser($database, $email, $password)
// PRE:  $database is an open MySQL database connection
//       $email is the email of a user to authenticate
//       $password is the password of a user to authenticate
// POST: FCTVAL == true if the user passes authentication with the supplied credentials, o.w. false
{
	$email = SanitizeString($email);
	$password = SanitizeString($password);
	$query = "SELECT pw_hash,pw_salt FROM users WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query);
	echo $result[0]['pw_salt'];
	echo HashPassword($password, $result[0]['pw_salt']);
	echo $result[0]['pw_hash'];
	if (HashPassword($password, $result[0]['pw_salt']) === $result[0]['pw_hash'])
	{
		return TRUE;
	}
	else
	{
		if (UnconfirmedUserExists($database, $email))
		{
			return "unconfirmed";
		}
		else
		{
			return FALSE;
		}
	}
}

function InitiateSession($database, $email)
// PRE:  $database is an open MySQL database connection
//       $email is the email for which to initiate a session
// POST: client side, a cookie exists called 'email' which is equivalent to $email
//       a session variable called 'session_key' is instantiated which carries one half
//       of the needed value to hash to verify the session
//       old session keys for user $email are deleted, and a new entry is created in the
//       table 'sessions' with id equal to the primary key of the row in 'users' containing
//       $email, and session key equal to the SHA-512 hash of the 'session_key' session variable
//       concatenated with the user's salt (also in the users table)
{
	$query = "SELECT id,pw_salt FROM users WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query);
	$id = $result[0]['id'];
	$salt = $result[0]['pw_salt'];
	$_SESSION['session_key'] = GenerateSalt();
	$query = "SELECT * FROM sessions WHERE id='$id'";
	$result = MySqlDatabaseQuery($database, $query);
	if (count($result) > 0)
	{
		$query = "DELETE FROM sessions WHERE id='$id';INSERT INTO sessions SET id='$id', session_key='" . HashPassword($_SESSION['session_key'], $salt) . "';";
		MySqlMultiQuery($database, $query, TRUE);
	}
	else
	{
		$query = "INSERT INTO sessions SET id='$id', session_key='" . HashPassword($_SESSION['session_key'], $salt) . "'";
		MySqlDatabaseQuery($database, $query, TRUE);
	}
	setcookie("email", $email);
}

function VerifySession($database)
// PRE:  $database is an open MySQL database connection
//       a 'session_key' session variable is initialized for this user
//       an 'email' cookie is initialized client side
//       a 'session_key' for this user exists in the 'sessions' table
// POST: FCTVAL == true if the client side cookie, the 'session_key' session
//       variable, and the 'session_key' field in 'sessions' for this user
//       agree, o.w. false
{
	$email = $_COOKIE['email'];
	$query = "SELECT id,pw_salt FROM users WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query);
	$id = $result[0]['id'];
	$salt = $result[0]['pw_salt'];
	$query = "SELECT session_key FROM sessions WHERE id='$id'";
	$result = MySqlDatabaseQuery($database, $query);
	// if hashed session key is equal to stored database value, session is authentic
	if ($result[0]['session_key'] === HashPassword($_SESSION['session_key'], $salt))
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}	
}

function EndSession($database)
// PRE:  $database is an open MySQL database connection
//       a 'session_key' session variable is initialized for this user
//       an 'email' cookie is initialized client side
//       a 'session_key' for this user exists in the 'sessions' table
// POST: the row in 'sessions' for this user is deleted
{
	$email = $_COOKIE['email'];
	$query = "SELECT id FROM users WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query);
	$id = $result[0]['id'];
	$query = "DELETE FROM sessions WHERE id='$id'";
	MySqlDatabaseQuery($database, $query);
	setcookie("email", "");
}

function ConfirmUser($database, $confirmCode)
// PRE:  $database is an open MySQL database connection
//       $confirmCode is a SHA-512 hash (if successful, this should be the 
//       code matched to a particular user in the 'users_unconfirmed' table)
// POST: the user in 'users_unconfirmed' with the confirmation code $confirmCode
//       is moved to the 'users' table
{
	$query = "SELECT * FROM users_unconfirmed WHERE confirm_code='$confirmCode'";
	$result = MySqlDatabaseQuery($database, $query);
	if (count($result) === 1)
	{
		$email = $result[0]['email'];
		$hash = $result[0]['pw_hash'];
		$salt = $result[0]['pw_salt'];
		$query = "INSERT INTO users SET email='$email', pw_hash='$hash', pw_salt='$salt';DELETE FROM users_unconfirmed WHERE confirm_code='$confirmCode';";
		$result = MySqlMultiQuery($database, $query, TRUE);
		return $result[0] && $result[1];
	}
	else
	{
		return FALSE;
	}
}

function SendConfirmation($database, $email)
// PRE:  $database is an open MySQL database connection
//       $email is the email of a user in the 'users_unconfirmed' table
// POST: an email is sent to $email containing a link to confirm their account
{
	// TODO: fix this function, make mail work
	$query = "SELECT confirm_code FROM users_unconfirmed WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query, TRUE);
	echo json_encode($result);
	if (count($result) === 1)
	{
		$confirmCode = $result[0]['confirm_code'];
		$to      = $email;
		$subject = 'Confirm your account with us';
		$message = 'Please confirm your account by visiting your unique confirmation link at <a href="104.236.196.20/confirm_user.php?id="' . $confirmCode . '>a href="104.236.196.20/confirm_user.php?id="' . $confirmCode . '</a>\'s';
		$headers = 'From: webmaster@jcrafts.xyz';
		return mail($to, $subject, $message, $headers);
	}
	else
	{
		return FALSE;
	}
}

function GenerateSalt()
// POST: FCTVAL == a random 32 character string to use as a salt, or as a random seed to an email confirmation code
{
	return mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
}

function HashPassword($password, $salt)
// PRE:  $password is some input password
//       $salt is a salt to prepend when hashing $password
// POST: FCTVAL == the hashed value of $salt . $password
{
	return hash("sha512", $salt . $password);
}

function UnconfirmedUserExists($database, $email)
// PRE:  $database is an open MySQL database connection
//       $email is a valid email
// POST: FCTVAL == true if the supplied email exists in the 'users_unconfirmed' table,
//       o.w. false
{
	$query = "SELECT email FROM users_unconfirmed WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query);
	if (count($result) > 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function ConfirmedUserExists($database, $email)
// PRE:  $database is an open MySQL database connection
//       $email is a valid email
// POST: FCTVAL == true if the supplied email exists in the 'users' table,
//       o.w. false
{
	$query = "SELECT email FROM users WHERE email='$email'";
	$result = MySqlDatabaseQuery($database, $query);
	if (count($result) > 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}
?>
