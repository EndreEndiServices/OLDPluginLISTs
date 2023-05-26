<?php

namespace PrestigeSociety\Levels\Task;

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class DespawnEntitiesTask extends PluginTask {

	private $entities = [];

	/**
	 *
	 * WelcomePlayerTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 * @param Entity[] $entities
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner, array $entities){
		parent::__construct($owner);
		$this->entities = $entities;

	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		foreach($this->entities as $entity){
			if($entity instanceof ItemEntity){
				$entity->close();
			}
		}
	}
}