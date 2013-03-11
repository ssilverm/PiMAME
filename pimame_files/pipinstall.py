#!/usr/bin/python
#installer - creates getip.sh - also adds or deletes getip.sh from rc.local
from optparse import OptionParser
import subprocess
import os
import sys


if os.getuid() == 0:
    print("Starting Up...")
else:
    print("You must run this script with sudo.")
    sys.exit()


parser = OptionParser()
parser.add_option('-r', '--remove', help='Remove PIP', dest='remove', default=False, action='store_true')
(options, args) = parser.parse_args()



def doInstall():
	print "Installing..."
	print "Creating Script..."
	createGetIPScript()
	print "Changing Permissions..."
	os.system('sudo chmod +x /etc/init.d/pipip.sh')
	print "Adding To BootUp Sequence..."
	os.system('sudo update-rc.d pipip.sh defaults')
	print "Testing Install..."
	os.system('sudo /etc/init.d/pipip.sh')
	print ""
	print "Finished!  To Remove, run this script with -r."

def doRemove():
	print "Removing..."
	os.system("sudo rm /etc/init.d/pipip.sh")
	os.system('sudo update-rc.d pipip.sh remove')
	print "Removed.  You may reinstall at any time."


def createGetIPScript():
	script = '''
	#!/bin/bash
### BEGIN INIT INFO
# Provides:          pipip
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Example initscript
# Description:       This file should be used to construct scripts to be
#                    placed in /etc/init.d.
### END INIT INFO
IP=$(/sbin/ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1')
URL="http://pip.sheacob.com/api.php?lan=${IP}"
curl ${URL}
'''
	try:
		# This will create a new file or **overwrite an existing file**.
		f = open("/etc/init.d/pipip.sh", "w")
		try:
			f.write(script) # Write a string to a file
			#f.writelines(lines) # Write a sequence of strings to a file
		finally:
			f.close()
	except IOError:
		print "There was an error writing the file."

if options.remove == True:
	doRemove()
else:
	doInstall()
