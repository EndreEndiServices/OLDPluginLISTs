<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class ClearInventoryCommand extends Command implements PluginIdentifiableCommand {


	/** @var PrestigeSocietyCore */
	private $plugin;

	/**
	 *
	 * EatCommand constructor.
	 *
	 * @param PrestigeSocietyCore $plugin
	 *
	 */
	public function __construct(PrestigeSocietyCore $plugin){
		parent::__construct("clearinventory", "Clear your inventory instantly!", RandomUtils::colorMessage("&e/ci"), ["ci"]);
		$this->setPermission("command.ci");
		$this->plugin = $plugin;
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 *
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){

			if(!$this->testPermission($sender)){
				return false;
			}

			$sender->getArmorInventory()->clearAll();
			$sender->getInventory()->clearAll();
			$sender->sendPopup(RandomUtils::colorMessage("&aInventory cleared!"));
		}

		return true;
	}

	/**
	 *
	 * @return Plugin
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}

}