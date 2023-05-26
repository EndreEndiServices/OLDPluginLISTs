<?php

namespace nlog\SmartUI\commands;

use pocketmine\command\PluginCommand;
use nlog\SmartUI\SmartUI;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class OpenUICommand extends PluginCommand{
	
	public function __construct(SmartUI $owner) {
		parent::__construct("smart", $owner);
		$this->setPermission(true);
		$this->setDescription("Ajutor SmartUI");
	}
	
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage(SmartUI::$prefix . "Comanda este numai pentru joc.");
			return true;
		}
        if (!$this->getPlugin()->getSettings()->canUseInWorld($sender->getLevel())) {
            $sender->sendMessage(SmartUI::$prefix . "Nu poti folosi.");
            return true;
        }
		$this->getPlugin()->getFormManager()->getMainMenuForm()->sendPacket($sender);
		return true;
	}
	
}