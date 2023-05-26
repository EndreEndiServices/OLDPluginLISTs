<?php

namespace SwirlPix\Signs\Task;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class TickColorfulSignsTask extends PluginTask {
	/** @var PrestigeSocietyCore */
	private $c;

	/**
	 *
	 * TickColorfulSignsTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct($c);
		$this->c = $c;
	}

	/**
	 *
	 * Actions to execute when run
	 *
	 * @param $currentTick
	 *
	 * @return void
	 *
	 */
	public function onRun($currentTick){

		//TODO
	}
}