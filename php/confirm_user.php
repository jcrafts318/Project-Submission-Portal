<?php
/* File Name:           confirm_user.php
 * Description:         This file contains a script to confirm a new user
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

if (ConfirmUser($database, $_GET['id']) === TRUE)		// confirm user using supplied code; if confirmation passes, redirect to confirmed page
{
	header('Location: ../confirmed.html') ;
}
else								// if redirection does not pass, redirect to confirm error page
{
	header('Location: ../confirm_error.html') ;
}
?>
