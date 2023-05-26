<?php

namespace PrestigeSociety\Optimizer;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Optimizer\Utils\OptimizerInfo;
use revivalpmmp\pureentities\entity\animal\walking\Cow;
use revivalpmmp\pureentities\entity\monster\Monster;
use slapper\entities\SlapperEntity;
use slapper\entities\SlapperHuman;
use slapper\entities\SlapperHusk;

class PrestigeSocietyOptimizer {

	public static function clearLag(){
		foreach(PrestigeSocietyCore::getInstance()->getServer()->getLevels() as $lvl){
			foreach($lvl->getEntities() as $ent){
				if(!($ent instanceof Player) and !($ent instanceof SlapperEntity) and !($ent instanceof SlapperHuman)){
					$ent->close();
				}
				OptimizerInfo::saveClearedEntity($ent);
			}
		}
		OptimizerInfo::addTimesCleared(1);
		PrestigeSocietyCore::getInstance()->getInfoParticles()->spawnToAll();
		PrestigeSocietyCore::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "gc");
	}

	public static function emergencyRestoreEntities(){
		OptimizerInfo::restoreAllEntities();
	}
}