<?php

namespace AI;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;


class AI extends PluginBase implements Listener{

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);

        Entity::registerEntity(Sheep::class, true, ['Sheep', 'minecraft:sheep']);

    }


}
