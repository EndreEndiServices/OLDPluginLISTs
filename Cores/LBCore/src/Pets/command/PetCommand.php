<?php

namespace Pets\command;

use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\CommandSender;
use LbCore\player\LbPlayer;

class PetCommand extends VanillaCommand {

	public function __construct($name) {
		parent::__construct(
				$name, "Enable/disable/change pet", "/pet param"
		);
		$this->setPermission("lbcore.command");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args) {
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (!($sender instanceof LbPlayer)) {
			return true;
		}

		if (!$sender->isAuthorized() || !$sender->isVip()) {
			$sender->sendLocalizedMessage("ONLY_FOR_VIP");
			return true;
		}
		if ($sender->getState() !== LbPlayer::IN_LOBBY) {
			$sender->sendLocalizedMessage("PET_ONLY_LOBBY");
			return true;
		}

		if (!isset($args[0])) {
			$sender->setPetState('toggle');
//			$sender->togglePetEnable();
			return true;
		}

		$arg = strtolower($args[0]);

		if ($arg == "yes" || $arg == "on") {
			$sender->setPetState('show');
//			$sender->showPet();
			return true;
		}

		if ($arg == "no" || $arg == "off") {
			$sender->setPetState('hide');
//			$sender->hidePet();
			return true;
		}

		$avilablePets = array("dog", "pig", "chicken");
		if (in_array($arg, $avilablePets)) {
			if ($arg == "dog") {
				$arg = "wolf";
			}
			$sender->setPetState('show', ucfirst($arg) . "Pet");
//			$sender->showPet(ucfirst($arg) . "Pet");
			return true;
		}

		$sender->togglePetEnable();
		return true;
	}

}
