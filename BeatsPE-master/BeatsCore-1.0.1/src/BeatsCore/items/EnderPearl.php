<?php

declare(strict_types=1);

namespace BeatsCore\items;

use pocketmine\item\{
    Item, ProjectileItem
};
use pocketmine\math\Vector3;
use pocketmine\Player;

use BeatsCore\Session;
use BeatsCore\Core;

class EnderPearl extends ProjectileItem{

    public function __construct($meta = 0, $count = 1){
        parent::__construct(Item::ENDER_PEARL, $meta, "Ender Pearl");
    }

    public function getProjectileEntityType() : string{
        return "EnderPearl";
    }

    public function getThrowForce() : float{
        return 1.1;
    }

    public function getMaxStackSize() : int{
        return 16;
    }

    public function onClickAir(Player $player, Vector3 $directionVector) : bool{
        $session = Core::getInstance()->getSessionById($player->getId());
        if($session instanceof Session){
            if(floor(microtime(true) - $session->lastEnderPearlUse) < Core::$enderPearlCooldown){
                return false;
            }else{
                $session->lastEnderPearlUse = time();
            }
        }
        return parent::onClickAir($player, $directionVector);
    }
}