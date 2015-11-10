# File Name:           server.py
# Description:         This file contains a server process that receives project processing
#                      requests from multiple local client processes instantiated by php scripts
#                      run by apache, then spawns a thread for each client and tests projects on
#                      those threads
# Dependencies:        network_lib.py, test.sh
# Additional Notes:    none

import socket
import sys
import network_lib
import os
import re
import thread
import start_daemon

def ProcessProject(tar, email, filename):
# PRE:  tar is the data contained in a tar file to be processed
#       email is the email of the student whose tar the input tar is
#       filename is the desired filename of the tar stored on the server
# POST: FCTVAL == the output of untarring and making the project
	# extract psu id
	id = re.match("[a-zA-Z]{3}[0-9]{1,4}", email)
	id = id.group(0)
	# create new folder for student if it does not exist
	os.system("rm -R " + id)
	os.system("mkdir " + id)
	# copy tar to working directory
	try:
		file = open(id + "/" + filename, 'w')
		file.write(tar)
		file.close()
	except:
		print "Error writing file. Ending script. (exited with code 2)"
		sys.exit(2)
	# untar and write to output file
	os.system("sh test.sh " + id + " " + filename)
	try:
		file = open(id + "/" + id + ".txt", 'r')
		output = file.read()
		file.close()
	except:
		print "Error opening file. Ending script. (exited with code 1)"
		sys.exit(1)
	return output

def ProcessThread(connection, flags):
# PRE:  connection is an active socket connecting the server to a client process
#       flags represents any special options passed to this function
#       function is called in a call to thread.start_new_thread()
# POST: this thread handles all requests from the client process, then closes the connection
#       and exits the thread
	print "Processing thread for connection", connection
	status = 0
	while status != "close":
		request = network_lib.ReceiveRequest(connection)
		# this call explained in the declaration of 'options'
		status, result = options[request[0]](connection, int(request[1]), int(request[2]))
		if (status == "email_set"):
			email = result
		elif (status == "filename_set"):
			filename = result
		elif (status == "project_set"):
			output = ProcessProject(result, email, filename)
	network_lib.SendRequest(connection, "output", len(output), 0)
	network_lib.SendBuf(connection, len(output), output)
	connection.close()
	thread.exit()

# request workflow options: each key maps to a specific function, so each request of type 'key' results in a call to function 'value'
options = {
	"connect" : network_lib.ConfirmConnection,
	"email" : network_lib.GetEmail,
	"filename" : network_lib.GetFilename,
	"project" : network_lib.GetProject,
	"status" : network_lib.GetStatus,
	"close" : network_lib.CloseConnection
}

# set up process as always on daemon service
start_daemon.createDaemon()

# create socket, get hostname, and bind server to localhost at port 14
s = socket.socket()
host = socket.gethostname()
port = 14
s.bind((host, port))
connections = 0

# listen for connections
s.listen(5)
while True:
	# accept new connection
	connection, address = s.accept()
	# spawn thread to handle this connection
	thread.start_new_thread(ProcessThread, (connection, 0))
