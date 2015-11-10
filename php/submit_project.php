<?php
/* File Name:           submit_project.php
 * Description:         This file contains a script which submits a project
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

// start session to access session variables, needed to access session token for auth
session_start();
if (VerifySession($database))	// if session token is valid for this user, process project
{
	// send project to server process and assign output to $response
	$response = shell_exec("python client.py " . $_COOKIE['email'] . " " . $_FILES['file']['tmp_name'] . " " . $_FILES['file']['name']);
	// if no response, connection to server could not be established
	if ($response === NULL)
	{
		header('Location: ../connection_error.html');
		die();
	}
	// validate server output
	$IsValidName = preg_match("/[a-zA-Z]+\-[a-zA-Z]{3}[0-9]{1,4}-assign2[targz\.]+/", $_FILES['file']['name']);
	$IsValidMake = TRUE;
	$IsValidMake = preg_match("/gcc -I. -c -g -Wall -I.   -o tagline_sim.o tagline_sim.c/", $response);
	$IsValidMake &= preg_match("/gcc -I. -c -g -Wall -I.   -o tagline_driver.o tagline_driver.c/", $response);
	$IsValidMake &= preg_match("/gcc -g tagline_sim.o tagline_driver.o  -o tagline_sim -lraidlib -lm -lcmpsc311 -L. -lgcrypt -lpthread -lcurl/", $response);
	$HasErrors = preg_match("/error/i", $response);
	$HasWarnings = preg_match("/warning/i", $response);
	// if project does not pass a test, redirect to submission error page
	if (!$IsValidName || !$IsValidMake || $HasErrors || $HasWarnings)
	{
		header('Location: ../submit_error.html');
	}
	// else redirect to submission success page
	else
	{
		header('Location: ../submit_success.html');
	}
}
else				// if session token is invalid for this user, redirect to session error page
{
	EndSession($database);
	header('Location: ../session_error.html');
}
?>
