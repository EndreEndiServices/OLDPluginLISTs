<?php

namespace TreasureHunt\command;

use pocketmine\command\defaults\VanillaCommand;
use pocketmine\math\Vector3;
use TreasureHunt\TreasureHunt;
use pocketmine\command\CommandSender;
use LbCore\player\LbPlayer;
use LbCore\LbCore;
use pocketmine\block\Air;

class ChestTPCommand extends VanillaCommand {
	public function __construct($name) {
		parent::__construct(
			$name,
			"Teleport player to chest with prize",
			"/chesttp"
		);
		$this->setPermission("lbcore.command");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		
		if (!($sender instanceof LbPlayer)) {
			return true;
		}

		// chech player name
		$playerName = strtolower($sender->getName());
		if (!$sender->isAuthorized() || !in_array($playerName, LbCore::$lbsgStaffNames)) {
			$sender->sendMessage('For lbsg staff only.');
			return true;
		}
		
		$chest = TreasureHunt::getTeeShirtChest();
		if (is_null($chest)) {
			return true;
		}
		
		// if all ok teleport player to chest
		if ($chest) {
			$chestCoords = explode(':', $chest->coords);
			$x = $chestCoords[0];
			$y = $chestCoords[1];
			$z = $chestCoords[2];
			$sender->setInvincible(false);
			$sender->setStateInGame();
			$level = $sender->getLevel();
			for ($dx = -2; $dx <= 2; $dx++) {
				for ($dz = -2; $dz <= 2; $dz++) {
					$chunk = $level->getChunk(($x + $dx) >> 4, ($z + $dz) >> 4);
					for ($dy = 0; $dy <= 2; $dy++) {
						$block = $level->getBlock(new Vector3($x + $dx, $y + $dy, $z + $dz));
						if ($block instanceof Air) {
							$block = $level->getBlock(new Vector3($x + $dx, $y + $dy - 1, $z + $dz));
							if ($block instanceof Air) {
								$sender->teleport(new Vector3($x + $dx, $y + $dy, $z + $dz));
								return true;
							}
						}
					}
				}
			}
			$newY = $level->getHighestBlockAt($x, $z) + 1;
			$sender->teleport(new Vector3($x, $newY, $z));
			$sender->sendMessage('The chest is by ' . ($newY - $y)  . ' blocks under you');
		} else {
			$sender->sendMessage('Problem with obtaining chest from db.');
		}
		
		return true;
	}
}
