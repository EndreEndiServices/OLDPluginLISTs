<?php

namespace PrestigeSociety\Kits\Special\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SKitCommand extends Command implements PluginIdentifiableCommand {
	/**
	 * @var PrestigeSocietyCore
	 */
	private $core;

	/**
	 *
	 * SKitCommand constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;
		parent::__construct('skit', '', RandomUtils::colorMessage('&eUsage: /skit <name>'), ['skits']);
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!($sender instanceof Player)) return;

		if(count($args) < 1){
			$sender->sendMessage($this->getUsage());

			return;
		}

		$kit = $args[0];

		if(strtolower($kit) === 'reset'){
			$this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->unloadKit($sender);
			$message = $this->core->getMessage('special_kits', 'successfully_reset');
			$sender->sendMessage(RandomUtils::colorMessage($message));

			return;
		}

		if($sender->hasPermission("kit." . strtolower($kit))){
			$result = $this->core->PrestigeSocietyKits->getSpecialKits()->getKitManager()->loadKit($sender, $kit);
			if($result === 3){
				$message = $this->core->getMessage('special_kits', 'already_enabled');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}elseif($result === 2){
				$message = $this->core->getMessage('special_kits', 'empty_inventory_first');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}elseif($result === 1){
				$message = $this->core->getMessage('special_kits', 'unknown_kit');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}elseif($result === 0){
				$message = $this->core->getMessage('special_kits', 'equipped_kit');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}else{
				$message = $this->core->getMessage('special_kits', 'unknown_error');
				$sender->sendMessage(RandomUtils::colorMessage($message));
			}
		}else{
			$message = $this->core->getMessage('special_kits', 'not_unlocked');
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}
	}

	/**
	 *
	 * @return PrestigeSocietyCore
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->core;
	}
}