<?php

declare(strict_types = 1);

namespace BeatsCore\entity;

use pocketmine\Server;
use pocketmine\entity\Entity;

//projectile
use BeatsCore\entity\projectile\EnderPearl;

class EntityManager extends Entity{

	public static function init(): void{
			//projectile
			self::registerEntity(EnderPearl::class, true, ['EnderPearl', 'minecraft:enderpearl']);
	}
}
