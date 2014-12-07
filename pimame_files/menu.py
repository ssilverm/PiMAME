#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Topmenu and the submenus are based of the example found at this location http://blog.skeltonnetworks.com/2010/03/python-curses-custom-menu/
# The rest of the work was done by Matthew Bennett and he requests you keep these two mentions when you reuse the code :-)
# Basic code refactoring by Andrew Scheller

import subprocess
import curses, os #curses is the interface for capturing key presses on the menu, os launches the files
from os import listdir
from os.path import isfile, join
import re
import pickle

screen = curses.initscr() #initializes a new window for capturing key presses
curses.noecho() # Disables automatic echoing of key presses (prevents program from input each key twice)
curses.cbreak() # Disables line buffering (runs each key as it is pressed rather than waiting for the return key to pressed)
curses.start_color() # Lets you use colors when highlighting selected menu option
screen.keypad(1) # Capture input from keypad

topLineNum = 0

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

DOWN = 1
UP = -1
OFFSET = 6
MENU = "menu"
COMMAND = "command"
EMU_CONTEXT = {
  'snes': {
      'dir': '/home/pi/roms/snes',
      'command': '/home/pi/emulators/pisnes/snes9x'
  },
  'genesis': {
      'dir': '/home/pi/roms/genesis',
      'command': '/home/pi/emulators/dgen-sdl-1.32/dgen'
  }
}

FAV_PATH = '/home/pi/fav.pkl'

def load_favorites():
  fav = []
  try:
    with open(FAV_PATH, 'rb') as pkl_file:
      fav = pickle.load(pkl_file)
  except IOError:
    pass
  return fav

def update_favorites(menu, parent, pos, is_add):
  if len(menu['options']) > pos and menu['options'][pos]['type'] == COMMAND:
    is_saved = -1
    i = 0
    for it in fav:
      if menu['options'][pos]['title'] == it['title']:
        is_saved = i
      i = i + 1
    
    need_update = False
    if is_add and is_saved == -1:
      fav.append(menu['options'][pos])
      need_update = True
    elif is_add == False and is_saved > -1:
      del fav[is_saved]
      need_update = True

    if (need_update):
      try:
        with open(FAV_PATH, 'wb+') as pkl_file:
          pickle.dump(fav, pkl_file)
      except IOError:
        pass
      menu_data['options'][fav_idx]['options'] = fav
      if not is_add:
        #runmenu(menu,parent)
  #processmenu(menu_data)
        return True
  return False

fav = load_favorites()
fav_idx = 5

def build_roms_menu(emulator):
  retval = [ {
  'title': f, 'type': COMMAND, 'command': EMU_CONTEXT[emulator]['command'] + ' \'' + EMU_CONTEXT[emulator]['dir'] + '/' + f +'\'' 
  } for f in listdir(EMU_CONTEXT[emulator]['dir']) if isfile(join(EMU_CONTEXT[emulator]['dir'],f)) ]

# print(retval)
  return retval

menu_data = {
  'title': "PiMAME Menu (v0.7.9)", 'type': MENU, 'subtitle':  "Please select an option...",
  'options': [
    { 'title': "Arcade", 'type': MENU, 'subtitle': "Arcade Emulators",
    'options': [
      { 'title': "AdvanceMAME", 'type': COMMAND, 'command': 'advmenu' },
      { 'title': "Neo Geo (GNGeo)", 'type': COMMAND, 'command': 'gngeo -i roms/' },
      { 'title': "MAME4All", 'type': COMMAND, 'command': '/home/pi/emulators/mame4all-pi/mame' },
      { 'title': "FBA (CPS1, CPS2, Neo Geo)", 'type': COMMAND, 'command': '/home/pi/emulators/fba/fbacapex' },
     ]
    },
    { 'title': "Consoles", 'type': MENU, 'subtitle': "Console Emulators",
    'options': [
      { 'title': "PlayStation 1 (PCSX_ReARMed)", 'type': COMMAND, 'command': '/home/pi/emulators/pcsx_rearmed/pcsx' },
#      { 'title': "Genesis (DGen)", 'type': COMMAND, 'command': 'advmenu -cfg advmenu-dgen.rc' },
      { 'title': "Genesis (DGen)", 'type': MENU, 'subtitle': "Genesis Roms",
         'options': build_roms_menu('genesis')
      },
      { 'title': "SNES (PiSNES / SNES9x)", 'type': MENU, 'subtitle': "SNES Roms",
         'options': build_roms_menu('snes')
      },
      { 'title': "NES (AdvanceMESS)", 'type': COMMAND, 'command': 'advmenu -cfg advmenu-nes.rc' },
      { 'title': "Gameboy (Gearboy Advmenu)", 'type': COMMAND, 'command': 'advmenu -cfg advmenu-gameboy.rc' },
      { 'title': "Gameboy Advance (gpsp)", 'type': COMMAND, 'command': '/home/pi/emulators/gpsp/gpsp' },
      { 'title': "Atari 2600 (Stella)", 'type': COMMAND, 'command': 'stella' },
      { 'title': "Commodore 64 (VICE)", 'type': COMMAND, 'command': 'x64' },
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
    { 'title': "Favorites", 'type': MENU, 'subtitle': 'Favorites',
    'options': fav
    },
  ]
}

def scrollwindow(increment, pos, optioncount):
    global topLineNum
    newPos = pos + increment

    # paging
    if increment == UP and pos == topLineNum and topLineNum != 0:
        topLineNum += UP 
        return
    elif increment == DOWN and newPos == (curses.LINES-OFFSET+topLineNum) and (topLineNum+curses.LINES-OFFSET) != optioncount+1:
        topLineNum += DOWN
        return

    # scroll highlight line
#     if increment == self.UP and (self.topLineNum != 0 or self.highlightLineNum != 0):
#         self.highlightLineNum = nextLineNum
#     elif increment == self.DOWN and (self.topLineNum+self.highlightLineNum+1) != self.nOutputLines and self.highlightLineNum != curses.LINES:
#         self.highlightLineNum = nextLineNum

# This function displays the appropriate menu and returns the option selected
def runmenu(menu, parent):
  global topLineNum
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
#       screen.erase()
      screen.clear() #clears previous screen on key press and updates display based on pos
      screen.border(0)
      
      screen.addstr(2,2, menu['title'], curses.A_STANDOUT) # Title for this menu
      screen.addstr(4,2, menu['subtitle'], curses.A_BOLD) #Subtitle for this menu

      # Display all the menu items, showing the 'pos' item highlighted
      top = topLineNum
      bottom = min(topLineNum+curses.LINES-OFFSET, optioncount)
      for index in range(top,bottom):
        textstyle = n
        if pos==index:
          textstyle = h
        screen.addstr(5+index-top,4, "%d - %s" % (index+1, menu['options'][index]['title']), textstyle)
      # Now display Exit/Return at bottom of menu
      textstyle = n
      if pos==optioncount:
        textstyle = h
      if bottom == optioncount:
        screen.addstr(5+optioncount-top,4, "%d - %s" % (optioncount+1, lastoption), textstyle)
      screen.refresh()
      # finished updating screen

    x = screen.getch() # Gets user input
    if x == ord('\n'):
      x = ord('c')

    # What is user input?
    if x >= ord('1') and len(str(optioncount+1)) == 1 and x <= ord(str(optioncount+1)):
      pos = x - ord('0') - 1 # convert keypress back to a number, then subtract 1 to get index
    elif x == 258: # down arrow
      if pos < optioncount:
        scrollwindow(DOWN, pos, optioncount)
        pos += 1
      else:
        pos = 0
        topLineNum = 0
    elif x == 8: # down arrow
      if pos < optioncount:
        scrollwindow(DOWN, pos, optioncount)
        pos += 1
      else:
        pos = 0
        topLineNum = 0
    elif x == 259: # up arrow
      if pos > 0:
        scrollwindow(UP, pos, optioncount)
        pos += -1
      else:
        pos = optioncount
        topLineNum = max(optioncount - curses.LINES + OFFSET + 1, 0)
    elif x == 259: # up arrow
      if pos > 0:
        scrollwindow(UP, pos, optioncount)
        pos += -1
      else:
        pos = optioncount
        topLineNum = max(optioncount - curses.LINES + OFFSET + 1, 0)
    elif x == ord('z') or x == ord('x'):
      is_add = True
      if menu_data['options'][fav_idx] == menu:
        is_add = False
      #print(is_add)
      need_refresh = update_favorites(menu, parent, pos, is_add)
      if (need_refresh):
        pos = optioncount
        break
      else:
        curses.flash()
    elif x != ord('\n'):
      curses.flash()

  # return index of the selected item
  return pos

# This function calls showmenu and then acts on the selected item
def processmenu(menu, parent=None):
  global topLineNum
  optioncount = len(menu['options'])
  exitmenu = False
  while not exitmenu: #Loop until the user exits the menu
    topLineNum = 0
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
