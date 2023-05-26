#!/bin/bash



#The MIT License (MIT)
#
#Copyright ShadowHost LLC (c) 2014 Marcus Naughton
#
#Permission is hereby granted, free of charge, to any person obtaining a copy
#of this software and associated documentation files (the "Software"), to deal
#in the Software without restriction, including without limitation the rights
#to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
#copies of the Software, and to permit persons to whom the Software is
#furnished to do so, subject to the following conditions:
#
#The above copyright notice and this permission notice shall be included in all
#copies or substantial portions of the Software.
#
#THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
#IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
#AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
#OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
#SOFTWARE.
#
# A very simple but effective graphical auto reboot script (Basically a server wrapper)
# Created by iamadpond iamadpond@naughton.ie
# Naughton.ie
#
# May not be used by commerical entities without prior permission from the address above.
# ----------------------------------------------
# Changelog
# 1.5  Added a backup Manager, Creates two backups "New" and "Old" Example:
#      Server stops, deletes OLD backup, renames current NEW backup to OLD, and creates a NEW backup.
# 
# 1.4: Added a Timer
# 1.3: Fixed bug in which pressing enter in the crash screen would stop the restart
# 1.2  Added Major ASCII art 
# 1.1  Adjusted Reboot timer from 7 seconds to 10
# 1.0  Intial Release
#
#                       HOW TO USE BACKUP MANAGER
#
# 1) PLEASE CREATE A FOLDER CALLED "world-backups", without quotes in a place of your chosing.
#
# 2) Create on your "world-backups" directory , two folders "world-OLD" and "world-NEW" Capitals DO matter!
#
# 3) PLEASE FILL IN PTW, PTW IS THE PATH TO YOUR WORLD FOLDER EG: /Pocketmine-MP/worlds/world
#
# 4) PLEASE FILL IN PTMI, PTMI IS THE PATH TO THE FOLDER CONDTAINING YOUR POCKETMINE start.sh FILE
#
# 5) PLEASE FILL IN WSD, WSD IS WHERE YOU MADE YOUR "world-backups" FOLDER
#
# 5) Run the server once until it has finished startup, then quit it twice with the keyboard combo "ctrl+c"
#
# 6) Should work!

PTMI=/Users/marcusnaughton/Desktop/Pocketmine/

WSD=/Users/marcusnaughton/Desktop/Pocketmine/world-backups

PTW=/Users/marcusnaughton/Desktop/Pocketmine/worlds/world

WO="world-OLD"

while true; do

#Backup Manager
cd $WSD
rm -rf $WO
mv world-NEW world-OLD
cp -R $PTW $WSD
mv world world-NEW

#Reboot
cd $PTMI
bash start.sh
clear
echo "      ===        -Server has stopped        ===      "
echo "     = | =      =====================      = | =     "
echo "    =  |  =     =     Server        =     =  |  =    "
echo "   =   |   =    =     Rebooting in  =    =   |   =   "
echo "  =    *    =   =     '10' SECONDS  =   =    *    =  "
echo " =============  =====================  ============= "
echo "             **Press CTRL + C to cancel**            "
echo "                    'What happened?'                 "
echo "   Server has been forced to stop or has encountered "
echo "     an error caused by a plugin or the current OS   "
for (( i=10; i>0; i--)); do
sleep 1 &
printf "Restarting in $i \r                 "
wait
done
clear
done
