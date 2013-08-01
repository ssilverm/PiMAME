#!/bin/bash

eIP=$(/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}')
wIP=$(/sbin/ifconfig wlan0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}')
blank=""

if [ "$eIP" != "$blank" ]; then
  echo "eth0 available"
  IP="$eIP"
elif [ "$wIP" != "$blank" ]; then
  echo "eth0 unavailable"
  echo "wlan0 available"
  IP="$wIP"
else
  echo "eth0 unavailable"
  echo "wlan0 unavailable"
  IP="$blank"
fi


#convert -size 200x30 xc:transparent -font /usr/share/fonts/truetype/ttf-dejavu/DejaVuSansMono.ttf -fill black -pointsize 12 -draw "text 5,15 'test'" test2.png
convert -size 200x60 xc:transparent -font /usr/share/fonts/truetype/ttf-dejavu/DejaVuSansMono.ttf -fill white -pointsize 12 -draw "text 5,55 '${IP}'" /home/pi/pimame_files/theip.png
composite -gravity south -dissolve 100 /home/pi/pimame_files/theip.png /home/pi/pimame_files/watermark_bg.png /home/pi/pimame_files/advmenu_background.png

