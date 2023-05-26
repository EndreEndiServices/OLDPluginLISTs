<?php

namespace PrestigeSociety\Restarter\Task;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Core\Utils\ServerUtils;

class RestartTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * RestartTask constructor.
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
	 * NonAPI
	 *
	 * @param $currentTick
	 *
	 */
	public function onRun($currentTick){
		try{
			$this->core->PrestigeSocietyRestarter->subtractTime(1);
			if($this->core->PrestigeSocietyRestarter->getTime() <= 0){
				$txt = $this->core->getMessage("restarter", "restart_message");
				$txt = RandomUtils::colorMessage($txt);

				foreach(ServerUtils::getOnPlayers() as $player){
					if($this->core->FunBox->isLSDEnabled($player)){
						$this->core->FunBox->toggleLSD($player);
					}
					if($this->core->PrestigeSocietyStaffMode->isInStaffMode($player)){
						$this->core->PrestigeSocietyStaffMode->unsetFromStaffMode($player);
					}
				}

				ServerUtils::kickAndShutDown($txt);
			}
			if($this->core->PrestigeSocietyRestarter->getTime() < 10){
				$txt = $this->core->getMessage("restarter", "count_down_message");
				$txt = RandomUtils::colorMessage($txt);
				$txt = RandomUtils::restarterTextReplacer($txt);
				ServerUtils::bcMessage($txt);
			}
		}catch(\Exception $e){
			$this->core->getLogger()->notice(
				RandomUtils::textOptions("Restarter error (Line: " . $e->getLine() . ", File: " . $e->getFile() . ", Date: " . date("jS of F, Y") . ""));
		}
	}
}