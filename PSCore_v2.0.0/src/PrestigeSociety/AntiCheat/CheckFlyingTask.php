<?php

namespace PrestigeSociety\AntiCheat;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\ServerUtils;

class CheckFlyingTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * CheckFlyingTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct($c);
		$this->core = $c;
	}

	/**
	 *
	 * @param $currentTick
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onRun($currentTick){
		foreach(ServerUtils::getOnPlayers() as $p){
			if($this->core->PrestigeSocietyAntiCheat->detectFlyingHack($p)){
				$this->core->getScheduler()->scheduleDelayedTask(new KickFlyingTask($this->core, $p), 20 * 10);
				if($this->core->getConfig()->getAll()["anti_cheat"]["log_hacking"]){
					$this->core->getLogger()->notice("[" . date("r") . "] I found a possible hacker: " . $p->getName() . ". I'm working to find out more ;)");
				}
			}
		}
	}
}