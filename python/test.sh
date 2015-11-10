#!/bin/bash

# File Name:           test.sh
# Description:         This file takes as arguments
#                          $1: the name of an output file to create
#                          $2: the name of a tar file in the working directory
#                      the script will untar the file $2, redirecting the output
#                      of stdout and stderr to $1.txt, then navigates down to
#                      the project folder itself and compiles the project, again
#                      redirecting stdout and stderr to $1.txt
# Dependencies:        none
# Additional Notes:    none

cd $1
tar xvzf $2 > $1.txt 2>&1
cd assign2
make clean >> ../$1.txt 2>&1
make >> ../$1.txt 2>&1
