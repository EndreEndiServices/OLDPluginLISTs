<?php

namespace PS;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use\pocketmine\event\entity\EntityDamageByEntityEvent;
use\pocketmine\level\sound\AnvilBreakSound;

class Main extends PluginBase implements Listener {

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

	public function onLoad(){
		$this->getLogger()->info("PS §aenabled");
	}

    public function onHit(EntityDamageEvent $event){
        $entity = $event->getEntity();
        $fizz = new AnvilBreakSound($entity);
        $entity->getLevel()->addSound($fizz);
     }
	
    public function onDisable(){
        $this->getLogger()->info("PS §cdisabled.");
        return true;
	}
}
