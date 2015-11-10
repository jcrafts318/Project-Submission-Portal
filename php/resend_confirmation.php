<?php
/* File Name:           resend_confirmation.php
 * Description:         This file contains a script which submits a project
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

// send confirmation email
SendConfirmation($database, $_POST['email']);
// redirect to confirm page
header('Location: ../confirm.html');
?>
