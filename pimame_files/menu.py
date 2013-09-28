#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Topmenu and the submenus are based of the example found at this location:
http://blog.skeltonnetworks.com/2010/03/python-curses-custom-menu/

The rest of the work was done by Matthew Bennett and he requests you keep
these two mentions when you reuse the code :-)
Basic code refactoring by Andrew Scheller
"""
import subprocess
import curses, os
import pygame
import sys
import subprocess
from signal import SIGTERM
import threading

pygame.init()
pygame.joystick.init()
js_count = pygame.joystick.get_count()
for i in range(js_count):
  js = pygame.joystick.Joystick(i)
  js.init()

screen = curses.initscr() 
curses.noecho() 
curses.cbreak() 
curses.start_color() 
screen.keypad(1) # Capture input from keypad

# Change this to use different colors when highlighting
# Sets up color pair #1, it does black text with white background 
curses.init_pair(1,curses.COLOR_BLACK, curses.COLOR_WHITE) 
h = curses.color_pair(1) #h is the coloring for a highlighted menu option
n = curses.A_NORMAL #n is the coloring for a non highlighted menu option

MENU = "menu"
COMMAND = "command"

menu_data = {
  'title': "PiMAME Menu (v0.7.9)", 'type': MENU,
    'subtitle':  "Please select an option...",
  'options': [
    { 'title': "Arcade", 'type': MENU, 'subtitle': "Arcade Emulators",
    'options': [
      { 'title': "AdvanceMAME", 'type': COMMAND, 'command': 'advmenu' },
      { 'title': "Neo Geo (GNGeo)", 'type': COMMAND, 'command': 'gngeo -i roms/' },
      { 'title': "MAME4All", 'type': COMMAND,
         'command': '/home/pi/emulators/mame4all-pi/mame' },
      { 'title': "FBA (CPS1, CPS2, Neo Geo)", 'type': COMMAND,
         'command': '/home/pi/emulators/fba/fbacapex' },
     ]
    },
    { 'title': "Consoles", 'type': MENU, 'subtitle': "Console Emulators",
    'options': [
      { 'title': "PlayStation 1 (PCSX_ReARMed)", 'type': COMMAND,
         'command': '/home/pi/emulators/pcsx_rearmed/pcsx' },
      { 'title': "Genesis (DGen)", 'type': COMMAND,
         'command': 'advmenu -cfg advmenu-dgen.rc' },
      { 'title': "SNES (PiSNES / SNES9x Advmenu)", 'type': COMMAND, 
         'command': 'zsnes' },
      { 'title': "NES (AdvanceMESS)", 'type': COMMAND,
         'command': 'advmenu -cfg advmenu-nes.rc' },
      { 'title': "Gameboy (Gearboy Advmenu)", 'type': COMMAND,
         'command': 'advmenu -cfg advmenu-gameboy.rc' },
      { 'title': "Gameboy Advance (gpsp)", 'type': COMMAND,
         'command': '/home/pi/emulators/gpsp/gpsp' },
      { 'title': "Atari 2600 (Stella)", 'type': COMMAND, 'command': 'stella' },
      { 'title': "Commodore 64 (VICE)", 'type': COMMAND, 'command': 'x64' },
     ]
    },
    { 'title': "CaveStory (NXEngine)", 'type': COMMAND,
       'command': '/home/pi/emulators/cs.sh' },
    { 'title': "ScummVM", 'type': COMMAND, 'command': 'scummvm' },
    { 'title': "Tools", 'type': MENU, 'subtitle': 'PIMAME',
    'options': [
      { 'title': "Install PIP (http://pip.sheacob.com/about.html)",
       'type': COMMAND, 'command': 'sudo /home/pi/pimame_files/pipinstall.py' },
      { 'title': "Remove PIP", 'type': COMMAND,
         'command': 'sudo /home/pi/pimame_files/pipinstall.py -r' },
      { 'title': "raspi-config", 'type': COMMAND, 'command': 'sudo raspi-config' },
      { 'title': "Reboot", 'type': COMMAND, 'command': 'sudo reboot' },
      { 'title': "Shutdown", 'type': COMMAND, 'command': 'sudo poweroff' },
     ]
    },
  ]
}

def run_menu(menu, parent):
  """ displays the appropriate menu and returns the option selected """

  # work out what text to display as the last menu option
  if parent is None:
    lastoption = "Exit (Return to Command Line)"
  else:
    lastoption = "Return to %s menu" % parent['title']

  optioncount = len(menu['options']) # how many options in this menu

  #pos is the zero-based index of the hightlighted menu option.  Every time
  #run_menu is called, position returns to 0, when run_menu ends the position
  #is returned and tells the program what option has been selected
  pos=0
  # used to prevent the screen being redrawn every time
  oldpos=None
  #control for while loop, let's you scroll through options
  #until return key is pressed then returns pos to program
  x = None 
  
  # Loop until return key is pressed
  while x !=ord('c'):
    if pos != oldpos:
      oldpos = pos
      screen.clear() 
      screen.border(0)
      screen.addstr(2,2, menu['title'], curses.A_STANDOUT)
      screen.addstr(4,2, menu['subtitle'], curses.A_BOLD)

      # Display all the menu items, showing the 'pos' item highlighted
      for index in range(optioncount):
        textstyle = n
        if pos==index:
          textstyle = h
        screen.addstr(5+index,4, "%d - %s" % (index+1,
                        menu['options'][index]['title']), textstyle)
      # Now display Exit/Return at bottom of menu
      textstyle = n
      if pos==optioncount:
        textstyle = h
      screen.addstr(5+optioncount,4, "%d - %s" % (optioncount+1, lastoption),
                    textstyle)
      screen.refresh()
      # finished updating screen

    #x = screen.getch() # Gets user input
    #Need to back with config file.  That's next up.
    if x == ord('\n'):
      x = ord('c')
    for event in pygame.event.get():
      if event.type == pygame.JOYBUTTONDOWN:
        if event.dict['button'] == 3:
          x = ord('c')
          curses.flash()
      if event.type == pygame.JOYAXISMOTION:
        d = event.dict
        if d['axis'] == 1 and d['value'] < 0:
          pos += -1
        elif d['axis'] == 1 and d['value'] > 0:
          pos += 1
  # return index of the selected item
  return pos

def kill_emu(pid):
  """
  Meant to be popped off in a thread to kill the emu with a button combo.
  Still working on this...it's probably bad.s
  """
  joys = [pygame.joystick.Joystick(i) for i in range(js_count)]
  for i in joys:
    i.init()
  while True:
    print i.get_button(8)
      #os.kill(pid, SIGTERM)
      #break
    
def process_menu(menu, parent=None):
  """ This function calls showmenu and then acts on the selected item """

  optioncount = len(menu['options'])
  exitmenu = False
  while not exitmenu: #Loop until the user exits the menu
    getin = run_menu(menu, parent)
    if getin == optioncount:
        exitmenu = True
    elif menu['options'][getin]['type'] == COMMAND:
      try:
        game = subprocess.Popen(menu['options'][getin]['command'].split(),
                            stderr = subprocess.PIPE, stdout = subprocess.PIPE)
        pid = game.pid
      except: pass
      #threading.Thread(target=kill_emu(pid)).start()
    elif menu['options'][getin]['type'] == MENU:
      process_menu(menu['options'][getin], menu) # display the submenu

# Main program  
def main():
  process_menu(menu_data)
  #VITAL!  This closes out the menu system and returns you to the bash prompt.
  curses.endwin() 

if __name__ == '__main__':
  main()
