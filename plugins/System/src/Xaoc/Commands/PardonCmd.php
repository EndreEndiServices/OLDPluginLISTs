<?php

namespace Xaoc\Commands;

use Xaoc\MainClass;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecuter;
use pocketmine\Player;
use pocketmine\Server;

class PardonCmd extends Command {
	public function __construct(MainClass $plugin) {
		$this->plugin = $plugin;
		parent::__construct("pardon", "Разбанить Игрока", "/pardon <игрок>", array("pardon"));
	}

	public function execute(CommandSender $sender, $alias, array $args) {
 		if(!$sender instanceof ConsoleCommandSender) {
			return true;
		}
			
		if($this->plugin->bandata->exists($args[0])){
			$this->plugin->bandata->remove($args[0]);
			$this->plugin->saveData();		
			$sender->sendMessage("§8[§cСистема§8]§f Вы успешно разбанили игрока§e $args[0]");	
			}else{
			$sender->sendMessage("§8[§cСистема§8]§f Игрок не найден");					
			}
			
	}

}

?>