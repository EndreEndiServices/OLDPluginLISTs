<?php

namespace PrestigeSociety\StaffMode;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class StaffModeCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	private $c;

	/**
	 * LandCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct("staffmode", "Enable staff mode", RandomUtils::colorMessage("&e/staffmode"), ["sm"]);
		$this->c = $c;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player) return;
		if($sender->hasPermission('command.sm')){
			if($this->c->PrestigeSocietyStaffMode->isInStaffMode($sender)){
				$sender->sendMessage(RandomUtils::colorMessage($this->c->getMessage('staff_mode', 'disabled')));
				$this->c->PrestigeSocietyStaffMode->unsetFromStaffMode($sender);
			}else{
				$sender->sendMessage(RandomUtils::colorMessage($this->c->getMessage('staff_mode', 'enabled')));
				$this->c->PrestigeSocietyStaffMode->setStaffModeReady($sender);
			}

		}else{
			$sender->sendMessage(TextFormat::RED . "You don't have permission run this Command.");
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->c;
	}
}