<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;

class LSDCommand extends Command implements PluginIdentifiableCommand {

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
		parent::__construct('lsd', 'get on an acid trip!', 'Usage: /lsd', ['acid']);
		$this->setPermission("command.lsd");
		$this->plugin = $c;
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
			if($sender->getLevel() == "Work") return true;

			$this->plugin->FunBox->toggleLSD($sender);
		}

		return true;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}