# File Name:           client.py
# Description:         This file contains a client process which is used to send a request to
#                      an always on server process that handles project testing, then returns
#                      the test output to the calling php script
# Dependencies:        network_lib.py
# Additional Notes:    none

import socket
import network_lib
import sys

# read in tar file
file = open(sys.argv[2], 'r')
tar = file.read()
file.close()

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

# send request to close connection, receive output
network_lib.SendRequest(s, "close", 0, 0)
request = network_lib.ReceiveRequest(s)
output = network_lib.ReceiveBuf(s, int(request[1]))
