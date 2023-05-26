<?php

namespace PrestigeSociety\Core\Task;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class HUDUpdateTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	protected $core;

	/**
	 *
	 * HUDUpdateTask constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		parent::__construct($core);
		$this->core = $core;
	}

	/**
	 *
	 * @param int $currentTick
	 *
	 */
	public function onRun(int $currentTick){
		$this->core->HUD->broadcastHUD();
	}

}