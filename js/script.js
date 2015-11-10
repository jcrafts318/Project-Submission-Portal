// File Name:           script.js
// Description:         This file contains the runtime script for index.html
// Dependencies:        php/authenticate.php, d3.js
// Additional Notes:    none

// display whichever prompt is needed
IsLoggedIn();

function SubmitPrompt()
// PRE:  document is loaded (taken care of HTML side by loading script at end of body)
// POST: submission prompt is displayed
{
	d3.select("#container").html("");
	d3.select("#container").append("div").attr("class", "prompt").append("h2").text("Please submit your project below")
	d3.select("#container").append("div").attr("class", "submit").html(
		'<form enctype="multipart/form-data" action="../php/submit_project.php" method="POST">' + 
		'<p>Project Tar File</p><input type="file" name="file" required/><br />' +
		'<input type="submit" value="Submit Project" /></form></br></br>' +
		'<form enctype="multipart/form-data" action="../php/logout.php" method="POST"><input type="submit" value="Log Out"/></form>')
}

function RegisterLoginPrompt()
// PRE:  document is loaded (taken care of HTML side by loading script at end of body)
// POST: register/login prompt is displayed
{
	d3.select("#container").append("div").attr("class", "prompt").append("h2").text("Please log in or register to begin")

	var login = d3.select("#container").append("div").attr("class", "login")
	var register = d3.select("#container").append("div").attr("class", "register")

	login = d3.select(".login")
	register = d3.select(".register")

	login.html('<h2>Log In</h2><form enctype="multipart/form-data" action="../php/login.php" method="POST">' + 
		'<p>Email Address</p><input type="text" name="email" required/>' +
		'<p>Password</p><input type="password" name="password" required/></br>' +
		'<input type="submit" value="Log In" /></form>')
	register.html('<h2>Register</h2><form enctype="multipart/form-data" action="../php/new_user.php" method="POST">' + 
		'<p>Email Address</p><input type="text" name="email" required/>' +
		'<p>Course Code</p><input type="text" name="course_code" required/>' +
		'<p>Password</p><input type="password" name="register_password" required/>' +
		'<p>Confirm Password</p><input type="password" name="confirm_password" required/></br>' +
		'<input type="submit" value="Register" /></form>')
}

function Authenticate()
// POST: FCTVAL == return value of php/authenticate.php
{
	return $.ajax({                                      
		url: "php/authenticate.php",
		dataType: "text"
	});
}

function IsLoggedIn()
// POST: if user is logged in, submission prompt is displayed, o.w. login/register prompt is displayed
{
	$.when(Authenticate()).done(function(retval){
		if (retval === "true")
		{
			SubmitPrompt();
		}
		else
		{
			RegisterLoginPrompt();
		}
	});
}
