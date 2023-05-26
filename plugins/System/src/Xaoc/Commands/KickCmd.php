<?php

namespace Xaoc\Commands;

use Xaoc\MainClass;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecuter;
use pocketmine\Player;
use pocketmine\Server;

class KickCmd extends Command {
	public function __construct(MainClass $plugin) {
		$this->plugin = $plugin;
		parent::__construct("kick", "Кикнуть Игрока", "/kick <игрок> <причина>", array("kick"));
	}

	public function execute(CommandSender $sender, $alias, array $args) {
 		if(!$sender->hasPermission("bansystem.kick")) {
			return true;
		}		
		
		if(count($args) < 2) {
			$sender->sendMessage("§8[§cСистема§8]§f Использование: §2/kick <игрок> <причина>");
			return true;
		}

		$kicked = $this->plugin->getServer()->getPlayer($args[0]);
	if($kicked instanceof Player) {		
		if($kicked->hasPermission("bansystem.antikick") && (!$sender instanceof ConsoleCommandSender || !$sender->isOp())){
			$sender->sendMessage("§8[§cСистема§8]§f Вы не можете кикнуть данного игрока");
			return true;
			}		
		unset($args[0]);
		$reason = implode(" ", $args);
			$this->plugin->getServer()->broadcastMessage("§8[§cСистема§8] §fИгрок §e".$sender->getName()." §fкикнул§f игрока §6".$kicked->getName()."§f. Причина: §b".$reason);
			$kicked->close("","§cВас кикнул игрок: §e".$sender->getName()."\n§cПричина: §e".$reason);
		}else{
			$sender->sendMessage("§8[§cСистема§8]§f Игрок не онлайн");
		}
	}

}

?>