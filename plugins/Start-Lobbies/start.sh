#!/bin/bash

echo "\nSTARTING SERVERS: (Y/n)"

read reply

if [ $reply = "Y" ]; then

	echo "\n'$reply' valid."
	echo "task started.\n"
    
    while read line; do   

        if [ $line != "---" ]; then
        	echo "$line"

        	screen -dmS $line /root/LightFishGames/Lobby/$line/start.sh

        else
        	echo "\ntask finished.\n"

        	exit 0

        fi

        sleep 300

    done < file_name_servers.txt

elif [ $reply = "n" ]; then

	echo "\n'$reply' valid."
	echo "task finished.\n"

	exit 0

else
	echo "\n'$reply' not valid."
	echo "task finish.\n"

	exit 0

fi
	