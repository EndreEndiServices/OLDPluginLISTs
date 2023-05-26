<?php
namespace MultiversePE\commands;

use MultiversePE\BaseCommand;
use MultiversePE\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class TPWorld extends BaseCommand {
	public function __construct(Main $plugin){
		parent::__construct($plugin, "tpworld", "Teleport to different worlds!", "/tpworld <world>", ["tpw", "worldtp", "wtp"]);
		$this->setPermission("multiversepe.teleport");
	}
	
	public function execute(CommandSender $sender, $alias, array $args){
		if(!$this->testPermission($sender)){
			$sender->sendMessage(TextFormat::Red . "[MultiversePE] You do not have permission to do that!");
		}else{
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::Red . "[MultiversePE] You can only use this command in-game!");
			}else{
				if(!isset($args[0])){
					$sender->sendMessage(TextFormat::Red . "[MultiversePE] Need help? Use /multiversepe help");
				}else{
					//TODO
				}
			}
		}
		return true;
	}
}
?>
