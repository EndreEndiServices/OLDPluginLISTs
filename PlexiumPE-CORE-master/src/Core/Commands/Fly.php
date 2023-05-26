<?php

namespace Core\Commands;

use Core\Loader;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class Fly extends PluginCommand {
	
	private $main;

    public function __construct($name, Loader $main) {
		
        parent::__construct($name, $main);
        $this->main = $main;
		
    }
	
	public function execute(CommandSender $sender, $commandLabel, array $args) {
		
		if(!$sender instanceof Player) {
			
			$sender->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "You must run this command in-game.");
		
		}
		
		if(count($args) < 1) {
			
            $sender->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GOLD . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "Usage: /fly (on/off)");
			return true;
			 
        }
		
		if($sender->hasPermission("core.command.fly") || $sender->isOp()) {
				
			if($sender instanceof Player) {
					
				if(isset($args[0])) {
						
					switch($args[0]) {
							
						case "on":
							
						$sender->setAllowFlight(true);
						$sender->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "Your ability to fly was enabled.");
							
						break;
							
						case "off":
							
						$sender->setAllowFlight(false);
						$sender->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "Your ablitiy to fly was disabled.");
							
						break;
							
					}
				}
			}
		}
		
		if(!$sender->hasPermission("core.command.fly")) {
					
			$sender->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "You don't have permission to use this command.");
				
		}
	}
}