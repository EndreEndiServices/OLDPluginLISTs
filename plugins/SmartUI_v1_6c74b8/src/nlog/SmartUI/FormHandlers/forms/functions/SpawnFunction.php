<?php

namespace nlog\SmartUI\FormHandlers\forms\functions;

use nlog\SmartUI\FormHandlers\SmartUIForm;
use nlog\SmartUI\SmartUI;
use pocketmine\Player;
use pocketmine\level\Position;

class SpawnFunction extends SmartUIForm {
	
	public static function getName(): string{
		return "Перемещение на спавн";
	}
	
	public static function getIdentifyName(): string{
		return "spawn";
	}
	
	public function sendPacket(Player $player) {
		$dlevel = $this->owner->getServer()->getDefaultLevel();
		$pos = new Position($dlevel->getSafeSpawn()->x, $dlevel->getSafeSpawn()->y, $dlevel->getSafeSpawn()->z, $dlevel);
		$player->teleport($pos);
		$player->sendMessage(SmartUI::$prefix . "Вы перемещены на спавн.");
	}
	
	protected function getFormData(Player $player) {
		//Not need
	}
	
	public function handleRecieve(Player $player, $result) {
		//Not need
	}
	
}