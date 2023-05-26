<?php

namespace PrestigeSociety\AntiCheat;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\AntiCheat\Handle\Session;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class KickFlyingTask extends PluginTask {

	/** @var Player */
	private $player;
	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * KickFlyingTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 * @param Player $p
	 *
	 */
	public function __construct(PrestigeSocietyCore $c, Player $p){
		parent::__construct($c);
		$this->player = $p;
		$this->core = $c;
	}

	/**
	 *
	 * @param $currentTick
	 *
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onRun($currentTick){
		try{
			if($this->core->PrestigeSocietyAntiCheat->detectFlyingHack($this->player)){
				$this->player->kick(RandomUtils::colorMessage($this->core->getMessage("anti_cheat", "kick_flying")));
				Session::deleteFlyingTask($this->player);
				if($this->core->getConfig()->getAll()["anti_cheat"]["log_hacking"]){
					$this->core->getLogger()->notice("[" . date("r") . "] I kicked a person flying without permission: " . $this->player->getName() . ".");
				}
			}
		}catch(\Exception $e){
			$this->core->getLogger()->notice(
				RandomUtils::textOptions("AntiCheat error (Line: " . $e->getLine() . ", File: " . $e->getFile() . ")"));
		}
	}
}