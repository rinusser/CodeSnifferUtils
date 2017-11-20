#!/bin/bash
#run this script to start live version of the image, to test code changes before they're built into a new image

alias phpcslive="sudo docker run --rm -u \"\$UID:\$(id -g)\" -v \"\$PWD:/app\" -v \"$PWD/src:/phpcs/src\" --name phpcs.\$(NOW) -it phpcs -ps --basepath=/app/"
