<?php
/* File Name:           new_user.php
 * Description:         This file contains a script to create a new user
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

// validate registration fields
$isValidEmail = preg_match('/[a-zA-Z]{3}[0-9]{1,4}\@psu\.edu/', $_POST['email']);				// must be abc####@psu.edu email
$isValidPassword = preg_match('/[a-z0-9*()^&%$#@!_?><., {}\"\';:|~`]{10,128}/i', $_POST['register_password']);	// must be 10-128 characters and composed of letters, numbers, and symbols
$isConfirmedPassword = $_POST['register_password'] === $_POST['confirm_password'];				// both password fields must be equivalent
$isValidCourseCode = $_POST['course_code'] === 'PSU311COURSE@F15';						// must be correct course code

// check all validation variables and then create user; if user creation fails or validation variables are incorrect, redirect to registration error page
if (!$isValidEmail || !$isValidPassword || !$isConfirmedPassword || !$isValidCourseCode ||
	!CreateUser($database, $_POST['email'], $_POST['register_password']))
{
	header('Location: ../register_error.html');
}
// if all validation variables pass and user creation succeeds, redirect to confirm page
else
{
	header('Location: ../confirm.html') ;
}
?>
