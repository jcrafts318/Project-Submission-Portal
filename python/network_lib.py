# File Name:           network_lib.py
# Description:         This file contains library functions for the network functionality
#                      of server.py and client.py
# Dependencies:        none
# Additional Notes:    none

import re

# Transport functions
def ConstructRequest(type, size, flags):
# PRE:  type is a type of request; must be one that is laid out 
#       in the "options" variable of the receiving host
#       size is the size of a buffer to be sent following the
#       request, or 0 if no buffer is to be sent
#       flags represents any special options for this request
# POST: FCTVAL == a string usable as a request to send across the network
	return type + ":" + str(size) + ":" + str(flags)

def ValidateResponse(connection, request, type, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       request is the request that has just been made
#       type, size, and flags, are the arguments used to
#       create the request
#       a request has just been passed to the host on the other side of
#       connection
# POST: if request is not equal to the request that would be constructed
#       using type, size, and flags, the connection is closed
#       NOTE: each request results in receiving the same request back from
#       the other host, so this should only be used in SendRequest to validate
#       that the request has been received successfully, as it will close the
#       connection if not
	if request != ConstructRequest(type, size, flags):
		print "Connection error has occured. Closing Connection."
		connection.close()
		exit(3)

def SendRequest(connection, type, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       type is a type of request; must be one that is laid out 
#       in the "options" variable of the receiving host
#       size is the size of a buffer to be sent following the
#       request, or 0 if no buffer is to be sent
#       flags represents any special options for this request
# POST: FCTVAL == response from the other host
#       if the response is valid, the other host is prepared to handle the request,
#       o.w. the connection is closed
	connection.send(ConstructRequest(type, size, flags))
	response = connection.recv(1024)
	ValidateResponse(connection, response, type, size, flags)
	return response

def ReceiveRequest(connection):
# PRE:  connection is an active socket connecting 2 hosts
# POST: FCTVAL == an array of size 3 containing type, size, and flags
#       of the received request
	request = connection.recv(1024)
	connection.send(request)
	return re.split(":", request)

def SendBuf(connection, size, buf):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size of the buffer to be sent
#       buf is the buffer itself
# POST: the buffer is received by the other host
	bytesSent = 0
	while (bytesSent < size):
		bytesSent += connection.send(buf[bytesSent:])

def ReceiveBuf(connection, size):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size of the buffer to be sent
# POST: FCTVAL == the data contained in the buffer sent by the other host
	bytesRemaining = size
	buf = ""
	while (bytesRemaining > 0):
		chunk = connection.recv(min(bytesRemaining, 1024))
		bytesRemaining -= len(chunk)
		buf += chunk
	return buf

# Request Handling Functions
def ConfirmConnection(connection, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size communicated by the preceding request
#       flags are the flags communicated by the preceding request
# POST: FCTVAL == the data contained in the buffer sent by the other host
	return "connected", 0

def GetEmail(connection, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size communicated by the preceding request
#       flags are the flags communicated by the preceding request
# POST: FCTVAL == a tuple containing the post-status of this function and
#       email sent from the other host
	email = ReceiveBuf(connection, size)
	return "email_set", email

def GetFilename(connection, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size communicated by the preceding request
#       flags are the flags communicated by the preceding request
# POST: FCTVAL == a tuple containing the post-status of this function and
#       filename sent from the other host
	filename = ReceiveBuf(connection, size)
	return "filename_set", filename

def GetProject(connection, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size communicated by the preceding request
#       flags are the flags communicated by the preceding request
# POST: FCTVAL == a tuple containing the post-status of this function and
#       tar sent from the other host
	tar = ReceiveBuf(connection, size)
	return "project_set", tar

def GetStatus(connection, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size communicated by the preceding request
#       flags are the flags communicated by the preceding request
# POST: FCTVAL == a tuple containing the post-status of this function and
#       status of this thread
	print "status check"
	status = 0
	return "status_check", status

def CloseConnection(connection, size, flags):
# PRE:  connection is an active socket connecting 2 hosts
#       size is the size communicated by the preceding request
#       flags are the flags communicated by the preceding request
# POST: FCTVAL == a tuple containing the post-status of this function and
#       0 as the result
	return "close", 0
