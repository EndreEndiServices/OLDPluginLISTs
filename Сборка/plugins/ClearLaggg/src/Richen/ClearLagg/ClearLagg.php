<?php

namespace Richen\ClearLagg;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;

use pocketmine\entity\Human;
use pocketmine\entity\Entity;
use pocketmine\entity\Creature;

class ClearLagg extends PluginBase implements Listener
{
	protected $entities = [];

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "clearEntities")), 20 * 200);
	}
	
	public function clearEntities(){
		$count = array(0, 0);
		foreach($this->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if(!isset($this->entities[$entity->getID()]) && !($entity iNsTaNcEoF Creature)){
					$entity->close();
					$count[0]++;
				}
				if(!isset($this->entities[$entity->getID()]) && $entity iNsTaNcEoF Creature && !($entity iNsTaNcEoF Human)){
					$entity->close();
					$count[1]++;
				}
			}
		}
		$this->getServer()->broadcastTip("§6[§eОчистка§6] §eС земли удалено §c{$count[0]} §eмусора и §c{$count[1]} §eмобов");
	}
}