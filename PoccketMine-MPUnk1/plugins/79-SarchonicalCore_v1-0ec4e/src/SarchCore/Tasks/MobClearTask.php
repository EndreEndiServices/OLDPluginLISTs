<?php

namespace SarchCore\Tasks;

use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use SarchCore\SarchCore;

class MobClearTask extends PluginTask {

	private $plugin;

	public function __construct(SarchCore $plugin) {
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}

	public function onRun(/*int */$currentTick) {
		foreach($this->plugin->getServer()->getLevels() as $level) {
			if(!$level instanceof Level) {
				continue;
			}
			foreach($level->getEntities() as $entity) {
				if($entity instanceof Player) {
					continue;
				}
				$entity->setHealth(0);
				continue;
			}
		}
	}
}
