<?php

namespace PrestigeSociety\Restarter\Task;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Core\Utils\ServerUtils;

class BroadcastTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * BroadcastTask constructor.
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
			if($this->core->PrestigeSocietyRestarter->getTime() > 10){
				$txt = $this->core->getMessage("restarter", "time_message");
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