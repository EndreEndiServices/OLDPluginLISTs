<?php

namespace PrestigeSociety\Core\Task;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class UpdateParticlesTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * WelcomePlayerTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner){
		parent::__construct($owner);
		$this->core = $owner;
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 *
	 */
	public function onRun(int $currentTick){
		$this->core->getInfoParticles()->updateParticles();
	}
}