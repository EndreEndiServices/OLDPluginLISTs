<?php

/* 
CommandManager Credit Goes To @CortexPE!
*/

declare(strict_types = 1);

namespace CrateUI\Commands;

use CrateUI\Main;

use pocketmine\command\Command;

use pocketmine\Server;

class CommandManager {

	public static function init(){
		$cmds = [
			new getkey("getkey"),
		];
		Server::getInstance()->getCommandMap()->registerAll("pocketmine", $cmds);
	}
	public static function overwrite(Command $cmd, string $commandName){
		// Thank you very much iksaku for leaving this method on the *good o'l* PocketMine Forums. :)
		$cmdMap = Server::getInstance()->getCommandMap();
		$cmdOverwrite = $cmdMap->getCommand($commandName);
		$cmdOverwrite->setLabel($cmdOverwrite->getLabel() . "__disabled");
		$cmdMap->unregister($cmdOverwrite);
		$cmdMap->register("pocketmine", $cmd);
	}
}
