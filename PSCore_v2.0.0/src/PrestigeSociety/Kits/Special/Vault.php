<?php

namespace PrestigeSociety\Kits\Special;

use pocketmine\Player;
use PrestigeSociety\Kits\Special\Kit\Kit;
use PrestigeSociety\Kits\Special\Utils\PlayerUtils;

class Vault {
	/**
	 *
	 * @var Kit[]
	 *
	 */
	public $players = [];

	/**
	 * @param Player $p
	 * @param        $kit
	 */
	public function setKit(Player $p, $kit){
		$this->players[PlayerUtils::PlayerHash($p)] = $kit;
	}

	/**
	 *
	 * @param Player $p
	 * @param        $kit
	 *
	 */
	public function setKitEnabled(Player $p, $kit){
		if(!isset($this->players[PlayerUtils::PlayerHash($p)])){
			$this->players[PlayerUtils::PlayerHash($p)] = $kit;
		}
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public function setKitDisabled(Player $p){
		if(isset($this->players[PlayerUtils::PlayerHash($p)])){
			unset($this->players[PlayerUtils::PlayerHash($p)]);
		}
	}

	/**
	 *
	 * @param Player $p
	 * @return Kit|null
	 *
	 */
	public function getPlayerKit(Player $p){
		if($this->isKitEnabled($p)) return $this->players[PlayerUtils::PlayerHash($p)];

		return null;
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public function isKitEnabled(Player $p){
		return isset($this->players[PlayerUtils::PlayerHash($p)]);
	}
}