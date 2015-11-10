<?php
/* File Name:           authenticate.php
 * Description:         This file contains a script which returns true if the session is still active, false ow
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

// start session to access session variables, needed to access session token for auth
session_start();
if (VerifySession($database))	// if session token is valid for this user, echo true
{
	echo "true";
}
else				// if session token is invalid, echo false
{
	echo "false";
}
?>
