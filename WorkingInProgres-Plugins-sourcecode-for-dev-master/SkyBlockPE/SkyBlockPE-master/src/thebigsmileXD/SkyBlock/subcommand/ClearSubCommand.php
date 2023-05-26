<?php

namespace thebigsmileXD\SkyBlock\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ClearSubCommand extends SubCommand{

	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("skyblock.command.clear");
	}

	public function getUsage(){
		return "";
	}

	public function getName(){
		return "clear";
	}

	public function getDescription(){
		return "Clear the island you are standing on";
	}

	public function getAliases(){
		return [];
	}

	public function execute(CommandSender $sender, array $args){
		if(!empty($args)){
			return false;
		}
		$player = $sender->getServer()->getPlayer($sender->getName());
		$plot = $this->getPlugin()->getPlotByPosition($player->getPosition());
		if($plot === null){
			$sender->sendMessage(TextFormat::RED . "You are not standing on an island");
			return true;
		}
		if($plot->owner !== $sender->getName() and !$sender->hasPermission("skyblock.admin.clear")){
			$sender->sendMessage(TextFormat::RED . "You are not the owner of this island");
			return true;
		}
		
		$economy = $this->getPlugin()->getEconomyProvider();
		$price = $this->getPlugin()->getLevelSettings($plot->levelName)->clearPrice;
		if($economy !== null and !$economy->reduceMoney($player, $price)){
			$sender->sendMessage(TextFormat::RED . "You don't have enough money to clear this island");
			return true;
		}
		
		if($this->getPlugin()->clearPlot($plot, $player)){
			$sender->sendMessage("Island is being cleared...");
		}
		else{
			$sender->sendMessage(TextFormat::RED . "Could not clear this island");
		}
		return true;
	}
}
