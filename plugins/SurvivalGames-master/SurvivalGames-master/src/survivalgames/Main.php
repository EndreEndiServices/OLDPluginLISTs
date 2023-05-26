<?php

namespace survivalgames;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use survivalgames\command\JoinArenaCommand;
use survivalgames\task\MapCopyTask;
use survivalgames\arena\Arean;
use survivalgames\arena\ArenaList;

class Main extends PluginBase {
	private $arenaList;
	private $commandMap = [];
	
	public function onEnable() {
		$this->arenaList = new ArenaList();
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if($command->getName() == "survivalgames") {
			if(!isset($args[0]) || trim($args[0]) == "") {
				$args[0] = "help";
			}
			if(isset($this->commandMap[$args[0]])) {
				$displayUsage = !$this->commandMap[$args[0]]->execute($sender, $args[0], array_slice($args, 1));
				if($displayUsage) {
					$sender->sendMessage("Usage: " . str_replace("sg", $label, $command->getUsage()));
				}
			}
		}
	}
	
	private function registerCommand(BaseCommand $command) {
		if(isset($commandMap[$command->getName()])) {
			return false;
		}
		$commandMap[$command->getName()] = $command;
		foreach($command->getAliases() as $a) {
			$commandMap[$a] = $command;
		}
		return true;
	}
	
}
