<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;

class SBanCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * SBanCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('sban', 'ban a player silently', 'Usage: /sban <player> [time] [reason...]', ['tempban']);
		$this->plugin = $c;
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if($sender->hasPermission('command.sban')){

			if(count($args) <= 0){
				$sender->sendMessage(TextFormat::RED . $this->getUsage());

				return;
			}

			if(count($args) < 2){
				$name = array_shift($args);
				if(($player = $this->plugin->getServer()->getPlayer($name)) instanceof Player){
					if($player->hasPermission("ban.bypass")){
						$sender->sendMessage(TextFormat::RED . $player->getDisplayName() . " can't be banned.");
					}else{
						$player->kick(TextFormat::RED . "You have been banned by " . $sender->getName() . ".", false);
					}
				}
				$this->plugin->getServer()->getNameBans()->addBan($name);
				$sender->sendMessage(TextFormat::RED . "Banned " . $name . " forever.");
			}elseif(count($args) >= 2){

				$name = array_shift($args);

				if($args[0] === '-1'){
					array_shift($args);
					$reason = implode(" ", $args);
					if(($player = $this->plugin->getServer()->getPlayer($name)) instanceof Player){
						if($player->hasPermission("ban.bypass")){
							$sender->sendMessage(TextFormat::RED . $player->getDisplayName() . " can't be banned.");
						}else{
							$player->kick(TextFormat::RED . "You have been banned by " . $sender->getName() . "." . (trim($reason) !== "" ? TextFormat::YELLOW . " Reason: " . TextFormat::RED . $reason : ""), false);
						}
					}
					$this->plugin->getServer()->getNameBans()->addBan($name);
					$sender->sendMessage(TextFormat::RED . "Banned " . $name . " forever." . (trim($reason) !== "" ? TextFormat::YELLOW . " Reason: " . TextFormat::RED . $reason : ""));
				}else{
					if(!($info = exc::stringToTimestamp(implode(" ", $args)))){
						$sender->sendMessage(TextFormat::RED . "Please specify a valid time.");

						return;
					}

					/** @var \DateTime $date */
					$date = $info[0];
					$reason = $info[1];

					if(($player = $this->plugin->getServer()->getPlayer($name)) instanceof Player){
						if($player->hasPermission("sban.bypass")){
							$sender->sendMessage(TextFormat::RED . $player->getDisplayName() . " can't be banned.");
						}else{
							$player->kick(TextFormat::RED . "You have been banned by " . $sender->getName() . " until " . $date->format("l, F j, Y") . " at " . $date->format("h:ia") . (trim($reason) !== "" ? TextFormat::YELLOW . " Reason: " . TextFormat::RED . $reason : ""), false);
						}
					}

					$sender->getServer()->getNameBans()->addBan(($player instanceof Player ? $player->getName() : $name), (trim($reason) !== "" ? $reason : null), $date, "PrestigeSocietyCore");
					$sender->sendMessage(TextFormat::RED . "Banned player " . ($player instanceof Player ? $player->getName() : $name) . " until " . $date->format("l, F j, Y") . " at " . $date->format("h:ia") . (trim($reason) !== "" ? TextFormat::YELLOW . " Reason: " . TextFormat::RED . $reason : ""));
				}

			}
		}
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}