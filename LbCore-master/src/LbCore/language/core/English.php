<?php
namespace LbCore\language\core;

use pocketmine\utils\TextFormat;
use LbCore\language\Translate;

class English {
	
	public $translates = array(
		
		/* Errors */
		"ERR_PLAYER_NOT_FOUND" => TextFormat::RED."We couldn't find a player by that name.",
		/* Economy */
		"YOUR_COINS" => TextFormat::GOLD."Your coins: " . TextFormat::WHITE . "arg1",
		"PLAYER_HAS_COINS" => TextFormat::WHITE."arg1 ".TextFormat::DARK_AQUA."has ".
							  TextFormat::WHITE."arg2 ".TextFormat::DARK_AQUA."coins.",
		"SENT_COINS" => TextFormat::DARK_AQUA."Sent ".TextFormat::WHITE."arg1 ".
						TextFormat::DARK_AQUA."coins to ".TextFormat::WHITE."arg2".TextFormat::DARK_AQUA.".",
		"ERR_INSUFFICENT_COINS" => TextFormat::RED."You don't have enough coins.",
		"NOT_REGISTERED" => TextFormat::RED."Register to do this",

		/* Login & Registration */
		"WELCOME_MESSAGE_REGISTERED" => TextFormat::DARK_AQUA."Welcome to ".
										TextFormat::AQUA."Life".TextFormat::RED."boat".TextFormat::DARK_AQUA."!\n".
										TextFormat::YELLOW."This username is already registered, please login\n".
										TextFormat::YELLOW."or change username in settings then type /register",
		"PASSWORD_CHANGED" => TextFormat::GREEN."Password changed.",
		"PASSWORD_NOT_CHANGED" => TextFormat::RED.'Something went wrong, password not changed',
		"NEEDS_LOGIN" => TextFormat::RED."Please log in first.",
		"ACCOUNT_LOCKED" => TextFormat::RED."Account Locked",
		"WELCOME_MESSAGE_UNREGISTERED" => TextFormat::DARK_AQUA."Welcome to ".
										  TextFormat::AQUA."Life".TextFormat::RED."boat".TextFormat::DARK_AQUA."!\n".
										  TextFormat::YELLOW."This account isn't registered, you can claim it\n".
										  TextFormat::YELLOW."with ".TextFormat::LIGHT_PURPLE."/register".TextFormat::YELLOW.".",
		"REGISTRATION_SUCCESS" => TextFormat::GREEN."You are now registered.",
		"ON_LOGIN" => TextFormat::GREEN."You are now logged in.",
		"CONFIRM_PASS" => TextFormat::YELLOW."Please type the new password again.",
		"SHORT_PASS" => TextFormat::RED."Please choose a longer password.",
		"PASS_NOT_MATCH" => TextFormat::RED."Passwords do not match. Try again.",
		"FINISH_REGISTRATION" => TextFormat::YELLOW."You are registering the account: ".TextFormat::WHITE."arg1\n".
								 TextFormat::YELLOW."With the password: ".TextFormat::WHITE."arg2\n".
								 TextFormat::YELLOW."Please type your email to finish registration.",
		"NEW_PASS" => TextFormat::YELLOW."Please type your desired new password.",
		"INVALID_EMAIL" => TextFormat::YELLOW."Please type a valid email.",
		"INCORRECT_PASSWORD" => TextFormat::DARK_AQUA."This account is registered, and that's not the\n".
								TextFormat::DARK_AQUA."right password. If you don't know the\n".
								TextFormat::DARK_AQUA."password, please pick a different MCPE username.",
		"SESSION_IN_USE" => TextFormat::DARK_AQUA."Your account is in use somewhere else, so you've been logged out.",
		"DEFAULT_USER" => TextFormat::LIGHT_PURPLE."You're using the default MCPE username.\n".
						  TextFormat::LIGHT_PURPLE."Please change it in the game settings.",
		"WAITING_FOR_LOGIN" => TextFormat::LIGHT_PURPLE."Waiting on login info for this account...",
		"VIP_GIVAWAY_WINNER" => TextFormat::DARK_PURPLE."You have won free VIP+ for this session!\\o/\n".
								TextFormat::DARK_PURPLE."Please log in.",
		"REGISTRATION_FAILED" => TextFormat::RED."Registration failed, please try again later.",
		"LOGIN_DB_ERROR" => TextFormat::BOLD.TextFormat::DARK_GRAY."." . 
			TextFormat::DARK_AQUA."Welcome to ".TextFormat::AQUA."Life".TextFormat::RED."boat".TextFormat::DARK_AQUA."!\n" .
			TextFormat::RED."There was an error with the database. If you were a registered or VIP+ user please quit and rejoin for your advantages." .
			TextFormat::BOLD.TextFormat::DARK_GRAY.".",

		/* Statistics */
		"STATISTIC_HEADER" => TextFormat::DARK_AQUA."Showing statistics for: ".TextFormat::WHITE."arg1",
		"KILL_DEATH_COUNTS" =>  TextFormat::DARK_AQUA."Kills: ".TextFormat::WHITE."arg1".
								TextFormat::DARK_AQUA.", Deaths: ".TextFormat::WHITE."arg2",
		"LAST_SEEN" => TextFormat::DARK_AQUA."Last seen playing on: ".TextFormat::WHITE,

		/* Antihacks/Maitnence/Server QoS */
		"IDLE_TIMEOUT" => TextFormat::YELLOW."You haven't joined a match in a while. Disconnected.",
		"IP_LIMITED" => TextFormat::YELLOW."Too many players from this IP logging in, try back later.",
		"ALREADY_AUTHENTICATED" => TextFormat::YELLOW."You're already logged in on this server.",
		"MOD_WARNING" => TextFormat::RED."Disable mods, they're not allowed on LBSG.",
		"LOW_PLAYER_COUNT" => TextFormat::LIGHT_PURPLE."Server has a low player count, forcing a restart.",
		"ABOUT_TO_RESTART" => TextFormat::DARK_AQUA."Server will restart in arg1 secondarg2...",
		"RESTARTING" => TextFormat::DARK_AQUA."Server is restarting...",

		/* Ranks */
		"VIP_ENABLED" => TextFormat::YELLOW."VIP items ".TextFormat::GREEN."enabled".TextFormat::GREEN.".",
		"VIP_DISABLED" => TextFormat::YELLOW."VIP items ".TextFormat::RED."disabled".TextFormat::RED.".",
		"VIP_USAGE" => TextFormat::YELLOW."Use /vip to re-enable",
		"RANKS_RETRIEVING_START" => TextFormat::LIGHT_PURPLE."Retrieving ranks from the database.",
		"RANKS_RETRIEVING_ERROR" => TextFormat::YELLOW."There was an error retrieving the ranks from the database, trying again",

		/* Chat Filter */
		"CANNOT_SAY_PASSWORD" => TextFormat::RED."Message blocked ".
								 TextFormat::YELLOW."(giving out your password is a\n".
								 TextFormat::YELLOW."bad idea and might get your account locked).",
		"MSG_INAPPROPRIATE" => TextFormat::RED."Message blocked by chat filter.",
		"NO_DATING" => TextFormat::RED."Please don't use LBSG as a dating service.",
		"NO_ADVERTISING" => TextFormat::RED."Please don't advertise.",
		"CIRCUMVENT_WARNING" => TextFormat::RED."Don't attempt to circumvent the chat filter.",
		"CHAT_WHEN_MUTED" => Translate::PREFIX_PLAYER_ACTION.TextFormat::LIGHT_PURPLE."You can't send chat messages while muted.\n".
							 Translate::PREFIX_PLAYER_ACTION.TextFormat::LIGHT_PURPLE."Use /unmute to enable chat again.",
		"COMMAND_WHEN_MUTED" => TextFormat::RED."You can't use commands while muted.\n".
								TextFormat::RED."Use /unmute to use commands again.",
		"RATE_LIMITED" => TextFormat::RED."Slow down, don't chat so quickly!",
		"MSG_SHORT" => TextFormat::RED."Type a longer message.",
		"MSG_REPEATED" => TextFormat::RED."Wait before repeating yourself.",

		/* Commands */
		"KITS_HEADER" => TextFormat::YELLOW."Available kits (page arg1 of arg2):",
		"HELP_TOO_FAR" => TextFormat::GRAY . "That page doesn't exist.",

		"CMD_DEFAULT_ACCOUNT" => Translate::PREFIX_ACTION_FAILED."You can't do that on this account.",
		"CMD_REQUIRE_LOGIN" => Translate::PREFIX_ACTION_FAILED."You need to log in or register first.",

		"MUTE_NO_MORE_CHAT" => Translate::PREFIX_PLAYER_ACTION."You will no longer receive player chat.\n" . 
								Translate::PREFIX_PLAYER_ACTION."To do so again, run ".
								TextFormat::LIGHT_PURPLE."/unmute".TextFormat::YELLOW.".",
		"UNMUTE_RECEIVE_ALL_CHAT" => Translate::PREFIX_PLAYER_ACTION."You will now receive all chat again.",
		"LBMSG_SPECIFY" => Translate::PREFIX_ACTION_FAILED."Please specify a player.",
		"LBMSG_INVALID" => Translate::PREFIX_ACTION_FAILED."Please choose a valid player to message.",
		"LBMSG_NO_PARTY" => Translate::PREFIX_PLAYER_ACTION."The party system is not running on this server.",
		"REGISTER_ALREADY_REGISTERED" => Translate::PREFIX_PLAYER_ACTION."This account has already been registered.\n" . 
										 Translate::PREFIX_PLAYER_ACTION."To register your own, change your MCPE username, \n" . 
										 Translate::PREFIX_PLAYER_ACTION."log into the server and run this command again.",
		"REGISTER_PASSWORD_TOO_SHORT" => Translate::PREFIX_ACTION_FAILED."Your password is too short." . 
										 Translate::PREFIX_ACTION_FAILED."Account not registered.",
		"REGISTER_REGISTER_PASSWORD" => Translate::PREFIX_PLAYER_ACTION."You are registering the account: arg1\n" . 
										Translate::PREFIX_PLAYER_ACTION."Please type the password you want to use in chat.\n" . 
										Translate::PREFIX_PLAYER_ACTION."It won't show to other players. You'll have to\n" . 
										Translate::PREFIX_PLAYER_ACTION."type this password before each game.",
		"REGISTER_REGISTER_EMAIL" => Translate::PREFIX_PLAYER_ACTION."You are registering the account: arg1\n".
									 Translate::PREFIX_PLAYER_ACTION."With the password: arg2\n".
									 Translate::PREFIX_PLAYER_ACTION."Please type your email to complete registration.",
		"LOGIN_ALREADY_LOGGED_IN" => Translate::PREFIX_ACTION_FAILED."You are already logged in.",
		"LOGIN_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /login <password>",
		"PARTY_HELP" => Translate::PREFIX_PLAYER_ACTION.TextFormat::AQUA.TextFormat::BOLD."Party system:\n".
						Translate::PREFIX_PLAYER_ACTION.TextFormat::YELLOW."/party".
						TextFormat::GOLD." <player name>".TextFormat::WHITE." | Send a party invite.\n".
						Translate::PREFIX_PLAYER_ACTION.TextFormat::YELLOW."/party".
						TextFormat::GOLD." accept <player name>".TextFormat::WHITE." | Accept a party invite.\n".
						Translate::PREFIX_PLAYER_ACTION.TextFormat::YELLOW."/party".
						TextFormat::GOLD." leave".TextFormat::WHITE." | Leave a party.",
		"PARTY_ACCEPT" => Translate::PREFIX_PLAYER_ACTION . TextFormat::GREEN . "Joining arg1's party...",
		"PARTY_ERROR" => Translate::PREFIX_PLAYER_ACTION."The party system is not running on this server.",
		"PARTY_LEAVE" => Translate::PREFIX_PLAYER_ACTION."Leaving party...",
		"PARTY_CREATE" => Translate::PREFIX_PLAYER_ACTION.TextFormat::GREEN."Creating party...\n" . 
						  Translate::PREFIX_PLAYER_ACTION.TextFormat::GREEN."Sending party invite to arg1...",
		"PARTY_INVITE" => Translate::PREFIX_PLAYER_ACTION.TextFormat::GREEN."Sending party invite to arg1...",
		"PARTY_INVITE_NO_PERMISSION" => Translate::PREFIX_ACTION_FAILED."Only party leaders can add players.",
		"CHAT_GLOBAL" => Translate::PREFIX_PLAYER_ACTION."Switched to global chat.",
		"CHAT_PARTY" => Translate::PREFIX_PLAYER_ACTION."Switched to party chat.",
		"CHAT_NO_PARTY" => Translate::PREFIX_ACTION_FAILED."You are not in a party!",
		"PCHAT_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /pchat <message>",
		"IGNORE_ADD" => Translate::PREFIX_PLAYER_ACTION."arg1 added to ignore list",
		"IGNORE_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /ignore <player name>",
		"UNIGNORE_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /unignore <player name>",
		"UNIGNORE_REMOVE" => Translate::PREFIX_PLAYER_ACTION."arg1 removed from ignore list",
		"UNIGNORE_ERROR" => Translate::PREFIX_ACTION_FAILED."arg1 not in ignore list",
		"BLOCK_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /block <player name>",
		"NOPE" => Translate::PREFIX_ACTION_FAILED."That command isn't available on this server.",
		"PAY_INVALID" => Translate::PREFIX_ACTION_FAILED."Please type a valid number of coins to send.",
		"PAY_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /pay <username> <amount>",
		"FRIEND_HELP" => TextFormat::AQUA.TextFormat::BOLD."Friend system:\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." list".
						 TextFormat::WHITE." | Show a list of your friends.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." <player name>".
						 TextFormat::WHITE." | Send a friend request.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." accept <player name>".
						 TextFormat::WHITE." | Accept a friend request.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." deny <player name>".
						 TextFormat::WHITE." | Deny a friend request.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." remove <player name>".
						 TextFormat::WHITE." | Remove a friend from your list.",
		"CHANGEPW_CHANGE" => Translate::PREFIX_PLAYER_ACTION."Changing password for: arg1\n".
							 Translate::PREFIX_PLAYER_ACTION."Please type your current password.",
		"CHANGEPW_ERROR" => Translate::PREFIX_ACTION_FAILED."You're already changing your password.",
		"LANG_CHANGE" => Translate::PREFIX_PLAYER_ACTION."Language set to English",
		"LANG_USAGE" => TextFormat::GRAY . "Usage: /lang <en|es|du|de>",
		"RANK_SHOW" => TextFormat::GRAY . "Your tag is shown",
		"RANK_HIDE" => TextFormat::GRAY . "Your tag is hidden",
		"STATS_IS_EMPTY" => TextFormat::RED . "Sorry, statistics for this player was not found",
		"REPLY_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage /reply <message>",
		"REPLY_TO_NOBODY" => Translate::PREFIX_ACTION_FAILED."You can not reply to nobody",
		"YOU_IGNORED" => Translate::PREFIX_ACTION_FAILED."You can not send messages to arg1",
		"TELL_USAGE" => Translate::PREFIX_ACTION_FAILED."Usage /tell <player> <message>",
		"ME_USAGE" => Translate::PREFIX_ACTION_FAILED."Usage: /me <action...>",
		"GIVE_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /give <item_name | item_id>",
		"WARN_USAGE" => TextFormat::RED . "Usage: /warn <player_name>",
		"WARNING_BEFORE_MUTE" => Translate::PREFIX_ACTION_FAILED."WARNING: We have detected inappropriate behavior. Please stop, or you will be muted",
		"WARN_NO_TARGET" => TextFormat::RED . "Player with name arg1 was not found online",
		
		/*Kit logic*/
		"ONLY_FOR_VIP" => TextFormat::RED . "This action is available only for VIP players.\n" . 
						  TextFormat::RED . "You can buy VIP rank in the Lifeboat+ app.",
		"WON_KIT" => TextFormat::DARK_AQUA.'You have been randomly given the '
					. TextFormat::DARK_PURPLE . 'arg1' . TextFormat::DARK_AQUA. ' kit',
		"VIP_CHANGE_KIT" => TextFormat::DARK_AQUA."VIP's can select a new Kit",
		"HAVE_KIT" => TextFormat::RED . "You already have this kit.",
		"VIP_SELECT_KIT" => TextFormat::DARK_BLUE . "-" .
							TextFormat::GREEN . 'You have selected the ' .
							TextFormat::DARK_PURPLE . 'arg1' . TextFormat::GREEN . ' kit',
		"TAP_TO_SELECT_KIT" => TextFormat::AQUA . 'Tap sign again to choose kit.',
		"GOT_SAVED_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . 'You got your saved kit ' 
					. TextFormat::GOLD . 'arg1' . TextFormat::GREEN . ' for today',
		"NO_KITS_FOUND" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Sorry, no available kits found",
		"UNKNOWN_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "That kit doesn't exist",
		"CHOOSE_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Usage /kits <info> <kit name>",
		"KITS_HELP" => TextFormat::AQUA.TextFormat::BOLD."Kits system:\n".
						TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " list".
						TextFormat::WHITE . " | Show a list of available kits.\n".
						TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " <kit name>".
						TextFormat::WHITE . " | Apply a kit.\n".
						TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " info <kit name>".
						TextFormat::WHITE . " | Show a kit description.",
		
		/*VIP Lounge*/
		"GOT_FOOD" => TextFormat::YELLOW . "You got arg1 arg2",
		"GOT_ENOUGH_FOOD" => TextFormat::RED . "You already have enough arg1!",
		"MAGIC_DRINK_WARNING" => TextFormat::RED . "Are you 21? We are not sure",
		"ENOUGH_CURE" => TextFormat::RED . "You got enough elixir of vitality for today. Go to maps and have fun!",
		"COFFEE_EFFECT" => TextFormat::GREEN . "Cool! You are rested and reinforced strength",
		
		"VIP_LOUNGE_ERROR" => array(		
			"Sorry, bud, VIP only!",
			"Only VIPs are allowed in this joint.",
			"Boss told me to keep out the riff-raff. Are you riff-raff?",
			"This is a respectable establishment. VIP only.",
			"The VIP lounge is for VIP",
			"You VIP?",
			"This is the VIP lounge.",
			"You gotta know someone to get in here, or be a VIP",
			"VIP lounge is for VIP only.",
			"You not gettin' in here without VIP",
			"I am bigger than you and you are not VIP",
			"VIP Lounge here"
		),
		
		/*Friend requests*/
		"PROFILE_NOT_FOUND" => TextFormat::RED."Couldn't find your player profile.",
		"PLAYER_NOT_EXIST" => TextFormat::RED."A player doesn't exist by that name.",
		"TRYING_ADD_YOURSELF" => TextFormat::RED."Sorry, imaginary friends don't count. You can't add yourself as a friend.",
		"INVALID_REQUEST_DATA" => TextFormat::RED."You don't have a request from that user.",
		"INCORRECT_HASH" => TextFormat::RED."Couldn't authenticate for your account.",
		"KNOWN_ERROR_PREFIX" => TextFormat::RED."An error has occurred:",
		"UNKNOWN_ERROR" => TextFormat::RED."An unknown error has occurred.",
		"FRIEND_ACCEPTED" => TextFormat::YELLOW."Accepted friend request from arg1.",
		"FRIEND_DENIED" => TextFormat::YELLOW."Denied friend request from arg1.",
		"FRIEND_LIST_REQUEST" => Translate::PREFIX_PLAYER_ACTION . "You have arg1 friend requests.\n" . 
								 Translate::PREFIX_PLAYER_ACTION . "Use /friend list to view them.",
		"YOUR_FRIENDS" => TextFormat::BOLD.TextFormat::YELLOW."Your friends:",
		"YOU_HAVE_NO_FRIENDS" => TextFormat::GRAY . "No friends to display. Use /friend to request friends.",
		"YOUR_REQUESTS" => TextFormat::BOLD.TextFormat::YELLOW."Your requests:",
		"YOU_HAVE_NO_REQUESTS" => TextFormat::GRAY . "No requests to display.",
		"FRIEND_REMOVED" => TextFormat::YELLOW . "Removed arg1 as a friend.",
		"FRIEND_REQUEST_SENT" => TextFormat::YELLOW . "Sent a friend request to arg1.",
		"ALREADY_FRIENDS" => TextFormat::RED . "You and this player already friends.",
		"DUPLICATE_REQUEST" => TextFormat::RED . "You already have friend request from this player.",
		"TOO_MANY_FRIENDS" => TextFormat::RED . "Someone has too many friends.",
		"MUST_UPDATE_PASS" => TextFormat::RED."Upgrading our security.\n".TextFormat::DARK_PURPLE. "Sorry for the inconvenience, you must change your password.",
                "PARTICLE_FOR_VIP" => "Sorry, particle effects are reserved for VIPs.",
                "PARTICLES" => TextFormat::BOLD . "arg1" . TextFormat::GREEN . " Particles",
                "CANT_TELEPORT_IN_DEATHMATCH" => TextFormat::RED."You're not allowed to use Teleporter in a death match",
                "CANT_PLACE_BLOCK" => TextFormat::RED."You can't place blocks here",
		"PET_ONLY_LOBBY" => TextFormat::GRAY . "You may have a pet only in lobby.",
		"PET_WELCOME" => array(
			"Welcome back!",
			"I have missed you.",
			"I am so happy to be here.",
			"Thank you for summoning me!",
			"We meet again...",
			"Hi.  What up?",
			"Hello, how have you been?",
			"Hello, Master!",
			"Howdy!",
			"Hey!",
			"G'day mate.",
			"What will we do today?",
			"Greetings, Master!  I am at your service!",
			"It's been a while, happy to see you!",
			"Sup?!",
			"Glad to have you back, Master!",
			"It's about time I see you again!",
			"Happy to keep you company.",
			"Glad to have you back!",
			"I'm happy to be accompanying you!",
			"Happy to be of service.",
			"Happy to keep you company, Master."
		),
		"PET_BYE" => array(
			"Okay, bye.",
			"Well, I will just find someone else.",
			"/human....../human.... not working.",
			"Oink.  or moo.  or whatever I am supposed to say.",
			"Well I was going to go get a snack anyway.",
			"Chao! (i am Italian)",
			"See ya!",
			"I think I see a squirrel I can go play with.",
			"It's been fun, thanks!",
			"Nap time!",
			"Time to go get food.",
			"Goodbye."
		),
		"PET_OWNER_DEAD" => array(
			"About this arg1 that killed you... should I bite him in the leg?",
			"I do not like arg1.",
			"That arg1 thinks he's all that.  We'll get him next time.",
			"Tough break, Master.",
			"I bet arg1 does not have a pet!",
			"Well, snap, I thought you had that one.",
			"Win some, lose some.",
			"I don't know how a legend like you even died?",
			"You were just warming up the thumbs.",
			"Don't worry that was just warm up.",
			"arg1 wish he had a pet as cool as me!"
		),
		"PET_OWNER_SOON_BACK" => array(
			"Back so soon?",
			"That was quick!",
			"I knew you would not be gone long.",
			"I hardly blinked and you were back.",
			"Did you forget something?"
		),
		"PET_OWNER_LONG_BACK" => array(
			"You were gone for ages!",
			"What took you so long?",
			"Can you just leave me like that!",
			"That was ages!",
			"Finally, you are back.",
			"Please don't leave me again!",
			"I missed you!",
			"I thought you were never coming back...",
			"Some people even have to take breaks from Lifeboat... sometimes.",
			"It's been a long daaay, without you my friend.",
			"That felt like an eternity!",
			"Gadzooks, how long to you expect me to wait?",
			"Next time don't be gone so long!",
			"I was getting hungry."
		),
		"PET_OWNER_RETURN" => array(
			"Welcome back!",
			"Hello again!",
			"So good to see you again",
			"Oh what fun we are having, aren't we?!",
			"Hello Hello Hello!",
			"Well good to see you again, Master!",
			"Hail, Master, well met!",
			"Did you miss me?",
			"I missed you.",
			"Well here we are again in the lobby",
			"Should we go to the VIP lounge?  I heat they have treats.",
			"So how was the game?",
			"Did you open any chests?",
			"Weirdest thing. You tapped a sign, and disappeared!",
			"What is on the other side of the sign wall, anyway?"
		),
		"PET_LOBBY_RANDOM" => array(
			"Got any food?",
			"Should you get in a game?",
			"Splendid weather we are having.",
			"Is that a squirrel over there?",
			"Come on, I want to play a game!",
			"Don't fall asleep, it's time to play!",
			"What does the fox say?",
			"Seen any good movies lately?"
		),
		"PET_CHAT_FILTER" => array(
			"Do you worry I might start to talk like that?",
			"I did not know that word.",
			"The opinions of my owner are not necessarily my opinions.",
			"I said something like that once but my mom said I shouldn't",
			"There are a lot of other good, descriptive words out there.",
			"Well that wasn't very nice.",
			"I feel like that sometimes too but I'm told to keep it to myself.",
			"Let all the anger out."
		),
		"PET_OWNER_WINS" => array(
			"You won!  Wow!  I can't believe it you won!",
			"Congratulations, Master!",
			"Another victory!",
			"Good job!",
			"That was awesome! Can we do another one?",
			"Wow, you looked like Katniss out there!"
		),
		"HACKER_USAGE" => Translate::PREFIX_PLAYER_ACTION."Usage: /hacker <report>",
		"REPORT_SEND" => "The report has been sent",
		"STATUE_DIRECT_CONNECT" => TextFormat::RED . "Please direct connect to " . TextFormat::GREEN . "arg1",
		"STATUE_WALLS" => TextFormat::RED . "Sorry, but this gamemode isn't currently in service.",
		"STATUE_COMING_SOON" => TextFormat::YELLOW . "This gamemode isn't done yet silly.",
		"TELL_ONESELF" => Translate::PREFIX_ACTION_FAILED . "You cannot direct message yourself.",
		"PLAYER_NOT_ONLINE" => Translate::PREFIX_ACTION_FAILED . "There's no player by that name online.",
		"CLASSIC_SG_VIP_KIT" => "Diamond Axe, Leather Cap, Chain Chestplate, Pants, Boots",
		"MIDAS_KIT" => "Gold Helmet, Chestplate, Leggings and Boots",
		"ARCHER_KIT" => "1 Bow, 16 Arrows",
		"TELEPORTER_KIT" => "1 Throwable Egg every minute, teleport where it lands",
		"BRAWLER_KIT" => "Give more knockback, take less.",
		"ATHLETE_KIT" => "Jump I",
		"PROSPECTOR_KIT" => "Diamond Pickaxe, Iron Helmet, Extra items in chests",
		"CREEPER_KIT" => "Throwable TNT (1x per 90s)",
		"TANK_KIT" => "Diamond Chestplate",
		"ASSASSIN_KIT" => "Iron Sword",
		"PARTICLES_OFF" => "Particles are off",
		"BOUNCER_NPC" => "Bouncer[NPC]",
		"PET_NPS" => "[NPC] Your Pet",
		"BAD_USERNAME" => TextFormat::RED . "This nickname does not comply with the Lifeboat policy. Try another one.",
		"SAME_PASS" => TextFormat::RED . "Your password is the same. Please use the different one when change it.",
	);

}
