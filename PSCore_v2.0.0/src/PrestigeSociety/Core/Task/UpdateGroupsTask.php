<?php

namespace PrestigeSociety\Core\Task;

use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class UpdateGroupsTask extends PluginTask {

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
	 */
	public function onRun(int $currentTick){
		$this->core->reloadGroupsConfig();
		$this->core->pruneGroupsConfig();

		$this->core->getLogger()->info(RandomUtils::colorMessage("&aUpdated and pruned all groups!"));
	}
}