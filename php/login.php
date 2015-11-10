<?php
/* File Name:           login.php
 * Description:         This file contains a script to login a user
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

$email = $_POST['email'];
$pw = $_POST['password'];

// start session to access session variables, needed to access session token for auth
$auth = AuthenticateUser($database, $email, $pw);

if ($auth === TRUE)		// if authentication passes with supplied credentials, instantiate a session
{
	// start session to access session variables
	session_start();
	InitiateSession($database, $email);
	// redirect to homepage (acts as login page if not logged in, ow project submission page)
	header('Location: ../index.html') ;
}
else if ($auth === "unconfirmed")		// if user matching supplied credentials is unconfirmed, redirect to unconfirmed page
{
	header('Location: ../unconfirmed.html') ;
}
else						// if login fails entirely, redirect to login error page
{
	header('Location: ../login_error.html');
}
?>
