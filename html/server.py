import socket
import sys
import network_lib
import os
import re

def ProcessProject(tar, email, filename):
	# extract psu id
	id = re.match("/[a-z]{3}[0-9]{1,4}/i", email)
	id = id.group(0)
	# create new folder for student if it does not exist
	folder = os.system("ls | egrep " + id)
	if (folder == ""):
		os.system("mkdir " + id)
	# copy tar to working directory
	print "Opening tar to extract data..."
	try:
		file = open(tar, 'r')
		content = file.read()
		file.close()
	except:
		print "Error opening file. Ending script. (exited with code 1)"
		sys.exit(1)
	try:
		file = open(id + "/" + filename, 'w')
		file.write(content)
		file.close()
	except:
		print "Error writing file. Ending script. (exited with code 2)"
		sys.exit(2)
	# untar and write to output file
	os.system("tar xvzf " + id + "/" + filename + " > " + id + "/" + id + "-assign3.txt")
	os.system("sh test.sh " + id)
	print "Opening output file to extract data..."
	try:
		file = open(id + ".txt", 'r')
		output = file.read()
		file.close()
	except:
		print "Error opening file. Ending script. (exited with code 1)"
		sys.exit(1)
	return output

# request workflow options: each key maps to a specific function, so each request of type 'key' results in a call to function 'value'
options = {
	"connect" : network_lib.ConfirmConnection,
	"email" : network_lib.GetEmail,
	"filename" : network_lib.GetFilename,
	"project" : network_lib.GetProject,
	"status" : network_lib.GetStatus,
	"close" : network_lib.CloseConnection
}
# init status variable
status = 0

# create socket, get hostname, and bind server to localhost at port 14
s = socket.socket()
host = socket.gethostname()
port = 14
s.bind((host, port))

s.listen(5)
while True:
	connection, address = s.accept()
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
	status = 0
