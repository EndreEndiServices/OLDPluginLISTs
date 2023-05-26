<?php

namespace PrestigeSociety\Optimizer;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class OptimizeTask extends PluginTask {

	/**
	 *
	 * OptimizeTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct($c);
	}

	/**
	 *
	 * @param $currentTick
	 *
	 */
	public function onRun($currentTick){
		PrestigeSocietyOptimizer::clearLag();
	}
}