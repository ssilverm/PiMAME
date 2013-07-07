#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Topmenu and the submenus are based of the example found at this location http://blog.skeltonnetworks.com/2010/03/python-curses-custom-menu/
# The rest of the work was done by Matthew Bennett and he requests you keep these two mentions when you reuse the code :-)
# Basic code refactoring by Andrew Scheller

import subprocess
import curses, os #curses is the interface for capturing key presses on the menu, os launches the files
screen = curses.initscr() #initializes a new window for capturing key presses
curses.noecho() # Disables automatic echoing of key presses (prevents program from input each key twice)
curses.cbreak() # Disables line buffering (runs each key as it is pressed rather than waiting for the return key to pressed)
curses.start_color() # Lets you use colors when highlighting selected menu option
screen.keypad(1) # Capture input from keypad

wlan = subprocess.check_output("/sbin/ifconfig wlan0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}' ", shell=True)
ether = subprocess.check_output("/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}' ", shell=True)

myip = ''
if wlan != '':
  myip += wlan
if ether != '':
  myip += ' ' + ether

if myip != '':
  myip = "Your IP is: " + myip + " - "


# Change this to use different colors when highlighting
curses.init_pair(1,curses.COLOR_BLACK, curses.COLOR_WHITE) # Sets up color pair #1, it does black text with white background 
h = curses.color_pair(1) #h is the coloring for a highlighted menu option
n = curses.A_NORMAL #n is the coloring for a non highlighted menu option

MENU = "menu"
COMMAND = "command"


menu_data = {
  'title': "PiMAME Menu", 'type': MENU, 'subtitle':  "Please select an option...",
  'options': [
    { 'title': "Arcade", 'type': MENU, 'subtitle': "Arcade Emulators",
    'options': [
      { 'title': "AdvanceMAME", 'type': COMMAND, 'command': 'advmenu' },
      { 'title': "Neo Geo (GNGeo)", 'type': COMMAND, 'command': 'gngeo -i roms/' },
      { 'title': "MAME4All", 'type': COMMAND, 'command': '/home/pi/emulators/mame4all-pi/mame' },
     ]
    },
    { 'title': "Consoles", 'type': MENU, 'subtitle': "Console Emulators",
    'options': [
      { 'title': "PlayStation 1 (PCSX_ReARMed)", 'type': COMMAND, 'command': '/home/pi/emulators/pcsx_rearmed/pcsx' },
      { 'title': "SNES (PiSNES / SNES9x Advmenu)", 'type': COMMAND, 'command': 'advmenu -cfg advmenu-snes.rc' },
      { 'title': "Gameboy (Gearboy Advmenu)", 'type': COMMAND, 'command': 'advmenu -cfg advmenu-gameboy.rc' },
      { 'title': "Atari 2600 (Stella)", 'type': COMMAND, 'command': 'stella' },
     ]
    },
    { 'title': "CaveStory (NXEngine)", 'type': COMMAND, 'command': '/home/pi/emulators/cs.sh' },
    { 'title': "ScummVM", 'type': COMMAND, 'command': 'scummvm' },
    { 'title': "Tools", 'type': MENU, 'subtitle': myip,
    'options': [
      { 'title': "Install PIP (http://pip.sheacob.com/about.html)", 'type': COMMAND, 'command': 'sudo /home/pi/pimame_files/pipinstall.py' },
      { 'title': "Remove PIP", 'type': COMMAND, 'command': 'sudo /home/pi/pimame_files/pipinstall.py -r' },
      { 'title': "raspi-config", 'type': COMMAND, 'command': 'sudo raspi-config' },
      { 'title': "Reboot", 'type': COMMAND, 'command': 'sudo reboot' },
      { 'title': "Shutdown", 'type': COMMAND, 'command': 'sudo poweroff' },
     ]
    },
  ]
}

# This function displays the appropriate menu and returns the option selected
def runmenu(menu, parent):

  # work out what text to display as the last menu option
  if parent is None:
    lastoption = "Exit (Return to Command Line)"
  else:
    lastoption = "Return to %s menu" % parent['title']

  optioncount = len(menu['options']) # how many options in this menu

  pos=0 #pos is the zero-based index of the hightlighted menu option.  Every time runmenu is called, position returns to 0, when runmenu ends the position is returned and tells the program what option has been selected
  oldpos=None # used to prevent the screen being redrawn every time
  x = None #control for while loop, let's you scroll through options until return key is pressed then returns pos to program
  
  # Loop until return key is pressed


  while x !=ord('c'):
    if pos != oldpos:
      oldpos = pos
      screen.clear() #clears previous screen on key press and updates display based on pos
      screen.border(0)
      screen.addstr(2,2, menu['title'], curses.A_STANDOUT) # Title for this menu
      screen.addstr(4,2, menu['subtitle'], curses.A_BOLD) #Subtitle for this menu

      # Display all the menu items, showing the 'pos' item highlighted
      for index in range(optioncount):
        textstyle = n
        if pos==index:
          textstyle = h
        screen.addstr(5+index,4, "%d - %s" % (index+1, menu['options'][index]['title']), textstyle)
      # Now display Exit/Return at bottom of menu
      textstyle = n
      if pos==optioncount:
        textstyle = h
      screen.addstr(5+optioncount,4, "%d - %s" % (optioncount+1, lastoption), textstyle)
      screen.refresh()
      # finished updating screen

    x = screen.getch() # Gets user input
    if x == ord('\n'):
      x = ord('c')

    # What is user input?
    if x >= ord('1') and x <= ord(str(optioncount+1)):
      pos = x - ord('0') - 1 # convert keypress back to a number, then subtract 1 to get index
    elif x == 258: # down arrow
      if pos < optioncount:
        pos += 1
      else: pos = 0
    elif x == 8: # down arrow
      if pos < optioncount:
        pos += 1
      else: pos = 0
    elif x == 259: # up arrow
      if pos > 0:
        pos += -1
      else: pos = optioncount
    elif x == 259: # up arrow
      if pos > 0:
        pos += -1
      else: pos = optioncount
    elif x != ord('\n'):
      curses.flash()

  # return index of the selected item
  return pos

# This function calls showmenu and then acts on the selected item
def processmenu(menu, parent=None):
  optioncount = len(menu['options'])
  exitmenu = False
  while not exitmenu: #Loop until the user exits the menu
    getin = runmenu(menu, parent)
    if getin == optioncount:
        exitmenu = True
    elif menu['options'][getin]['type'] == COMMAND:
      os.system(menu['options'][getin]['command']) # run the command
    elif menu['options'][getin]['type'] == MENU:
      processmenu(menu['options'][getin], menu) # display the submenu

# Main program  
processmenu(menu_data)
curses.endwin() #VITAL!  This closes out the menu system and returns you to the bash prompt.
