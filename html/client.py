import socket
import network_lib
import sys

try:
	file = open(sys.argv[2], 'r')
	tar = file.read()
	file.close()
except:
	print "Error opening file. Ending script. (exited with code 1)"
	sys.exit(1)

# create socket, get hostname, and bind server to localhost at port 14
s = socket.socket()
host = socket.gethostname()
port = 14

# connect to server
s.connect((host, port))

# send connection confirmation request to server
network_lib.SendRequest(s, "connect", 0, 0)

# send student name
network_lib.SendRequest(s, "email", len(sys.argv[1]), 0)
network_lib.SendBuf(s, len(sys.argv[1]), sys.argv[1])

# send output file name
network_lib.SendRequest(s, "filename", len(sys.argv[3]), 0)
network_lib.SendBuf(s, len(sys.argv[3]), sys.argv[3])

# send file as buffer
network_lib.SendRequest(s, "project", len(tar), 0)
network_lib.SendBuf(s, len(tar), tar)

# close connection
network_lib.SendRequest(s, "close", 0, 0)
request = network_lib.ReceiveRequest(s)
output = network_lib.ReceiveBuf(s, int(request[1]))
print output
