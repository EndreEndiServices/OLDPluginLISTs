<?php

namespace Core\Fly;

use Core\Loader;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class FlyEvent implements Listener {
	
	public function onDamage(EntityDamageEvent $event) {
		
		if($event instanceof EntityDamageByEntityEvent){
			
			$entity = $event->getEntity();
			$damager = $event->getDamager();
			
			if($entity instanceof Player && $damager instanceof Player) {
				
				if($damager->isFlying()) {
					
					$damager->setFlying(false);
					$damager->setAllowFlight(false);
					$damager->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "Flight was disabled in combat mode!");
					
				}

				if($entity->isFlying()) {
					
					$entity->setFlying(false);
					$entity->setAllowFlight(false);
					$entity->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "Flight was disabled in combat mode!");
					
				}
			}
		}
	}
}	