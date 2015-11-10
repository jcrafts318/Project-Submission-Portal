<?php
/* File Name:           logout.php
 * Description:         This file removes the session key from the database and resets the client side cookie
 * Dependencies:        easy_auth_library.php, easy_auth_toolbox.php, connect.php
 * Additional Notes:    none
 */

require_once("easy_auth_library.php");
require_once("connect.php");	// instantiates $database

// remove active session hash from DB
EndSession($database);
// redirect to login page
header('Location: ../index.html');
?>
