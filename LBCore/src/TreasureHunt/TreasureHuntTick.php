<?php

namespace TreasureHunt;

use pocketmine\scheduler\PluginTask;
use TreasureHunt\TreasureHunt;

class TreasureHuntTick extends PluginTask {

	private $teeShirt;
	
	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->teeShirt = TreasureHunt::getInstance();
	}

	public function onRun($currentTick) {
		$this->teeShirt->checkPrize();
	}

}
