<?php
namespace MultiversePE\commands;

use MultiversePE\BaseCommand;
use MultiversePE\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class MultiversePE extends BaseCommand {
	public function __construct(Main $plugin){
		parent::__construct($plugin, "multiversepe", "MultiversePE Admin Commands!", "/multiversepe <create>", ["mvpe", "mvp", "multiverse"]);
		$this->setPermission("multiversepe.admin");
	}
	
	public function execute(CommandSender $sender, $alias, array $args){
		if(!$this->testPermission($sender)){
			$sender->sendMessage(TextFormat::Red . "[MultiversePE] You do not have permission to do that!");
		}else{
			if(!isset($args[0])){
				$sender->sendMessage(TextFormat::Red . "[MultiversePE] Need help? Use /multiversepe help");
			}else{
				if($args[0] == "help"){
					$sender->sendMessage("MultiversePE Commands:");
					$sender->sendMessage("/multiversepe <create> <world> - Create a new world");
					$sender->sendMessage("/tpworld <world> - Teleport to different worlds!");
				}
			}
		}
		return true;
	}
}
?>
