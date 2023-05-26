<?php

namespace Xaoc\Commands;

use Xaoc\MainClass;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecuter;
use pocketmine\Player;
use pocketmine\Server;

class BanCmd extends Command {
	public function __construct(MainClass $plugin) {
		$this->plugin = $plugin;
		parent::__construct("ban", "Забанить Игрока", "/ban <игрок> <причина>", array("ban"));
	}

	public function execute(CommandSender $sender, $alias, array $args) {
 		if(!$sender->hasPermission("bansystem.ban")) {
			return true;
		}
			
		if($this->plugin->bantime->exists(strtolower($sender->getName())) && $this->plugin->bantime->get(strtolower($sender->getName())) > time()) {
			$time = $this->plugin->bantime->get(strtolower($sender->getName())) - time();					
			$sender->sendMessage("§8[§cСистема§8] §fВремя до следующего использования: §e".($time/3600%24)." §fч. §e".($time/60%60)." §fмин.");						
			return true;
		}

		if(count($args) < 2) {
			$sender->sendMessage("§8[§cСистема§8] §fИспользование: §2/ban <игрок> <причина>");
			return true;
		}

		$banned = $this->plugin->getServer()->getPlayer($args[0]);
	if($banned instanceof Player) {		
		if($banned->hasPermission("bansystem.antiban") && (!$sender instanceof ConsoleCommandSender || !$sender->isOp())){
			$sender->sendMessage("§8[§cСистема§8]§f Вы не можете забанить данного игрока");
			return true;
			}
		unset($args[0]);
		$reason = implode(" ", $args);
			$this->plugin->getServer()->broadcastMessage("§8[§cСистема§8]§f Игрок §e".$sender->getName()." §fдал бан игроку §6".$banned->getName()."§f. Причина: §b".$reason);
			$banned->close("","§cВы получили бан от игрока: §e".$sender->getName()."\n§cПричина: §e".$reason);
			$this->plugin->bandata->setNested(strtolower($banned->getName()).".who", $sender->getName());
			$this->plugin->bandata->setNested(strtolower($banned->getName()).".reason", $reason);											
			$this->plugin->saveData();
			if(!$sender instanceof ConsoleCommandSender || !$sender->isOp()) {
				$this->plugin->bantime->set(strtolower($sender->getName()), time() + 3600);
				$this->plugin->bantime->save();				
			}
		}else{
			$sender->sendMessage("§8[§cСистема§8] §fИгрок не онлайн");
		}
	}

}

?>