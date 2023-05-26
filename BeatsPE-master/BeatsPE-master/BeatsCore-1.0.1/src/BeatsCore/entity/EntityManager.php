<?php

declare(strict_types=1);

namespace BeatsCore\entity;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\entity\Entity;

use BeatsCore\entity\projectile\EnderPearl;

use BeatsCore\Core;

class EntityManager extends Entity{

    public static function start() : void{
        self::registerEntity(EnderPearl::class, true, ['EnderPearl', 'minecraft:enderpearl']);
    }
}